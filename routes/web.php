<?php

use App\Http\Controllers\CmsPageController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\I18nController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\WebhookController;
use App\Models\CmsPage;
use App\Models\BlogPost;
use App\Models\SystemSetting;
use App\Services\StorageManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Home / Landing
Route::get('/', [LandingController::class, 'index'])->name('home');

// Auth routes
require __DIR__.'/auth.php';

// Locale / currency / theme
Route::put('/locale', [LocaleController::class, 'update'])->name('locale.update');
Route::get('/i18n/{locale}', [I18nController::class, 'show'])->name('i18n.show');
Route::put('/currency', [CurrencyController::class, 'update'])->name('currency.update');
Route::post('/theme/update', [ThemeController::class, 'update'])->name('theme.update');

// Branding assets are served through the application so logo/favicon updates do
// not depend on a public storage symlink or web-server alias.
Route::get('/branding/{type}', function (string $type) {
    $map = [
        'logo' => ['app_logo_path', 'app_logo_disk'],
        'favicon' => ['app_favicon_path', 'app_favicon_disk'],
    ];

    if (! isset($map[$type])) {
        abort(404);
    }

    [$pathKey, $diskKey] = $map[$type];
    $path = SystemSetting::get($pathKey);
    if (! $path) {
        abort(404);
    }

    $disk = SystemSetting::get($diskKey, 'public');
    app(StorageManager::class)->ensureDiskReady($disk);

    if (! Storage::disk($disk)->exists($path)) {
        abort(404);
    }

    return Storage::disk($disk)->response($path, null, [
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->name('branding.asset');

// Public marketing pages
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}/image', [BlogController::class, 'image'])->name('blog.image');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Public marketing landing pages
Route::get('/pricing', [LandingController::class, 'pricing'])->name('pricing');
Route::get('/faq', [LandingController::class, 'faq'])->name('faq');
Route::get('/use-cases', [LandingController::class, 'useCases'])->name('use-cases');
Route::get('/about', [LandingController::class, 'about'])->name('about');
Route::get('/integrations', [LandingController::class, 'integrations'])->name('integrations');

// CMS pages (e.g. /p/privacy, /p/terms)
Route::get('/p/{slug}', [CmsPageController::class, 'show'])->name('cms-page.show');

// Sitemap & robots.txt
Route::get('/sitemap.xml', function () {
    $landingEnabled = true;
    try {
        $landingEnabled = \App\Models\SystemSetting::get('landing.page_enabled', '1') === '1';
    } catch (Throwable $e) {
        // table may not exist yet
    }

    $entries = [];
    $push = static function (string $loc, ?string $lastmod = null, string $changefreq = 'weekly', string $priority = '0.5') use (&$entries): void {
        $entries[$loc] = [
            'loc' => $loc,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    };

    if ($landingEnabled) {
        $push(url('/'), null, 'daily', '1.0');
        foreach ([url('/pricing'), url('/faq'), url('/use-cases'), url('/about'), url('/integrations'), url('/contact')] as $loc) {
            $push($loc, null, 'weekly', '0.8');
        }
    }

    try {
        $cmsPages = CmsPage::where('published', true)->get();
        foreach ($cmsPages as $page) {
            $push(route('cms-page.show', $page->slug), $page->updated_at?->toAtomString(), 'monthly', '0.7');
        }
        $blogPosts = BlogPost::where('published', true)->get();
        foreach ($blogPosts as $post) {
            $push(route('blog.show', $post->slug), $post->updated_at?->toAtomString(), 'monthly', '0.7');
        }
    } catch (Throwable $e) {
        // table may not exist yet
    }

    ksort($entries);

    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($entries as $entry) {
        $xml .= '<url><loc>'.htmlspecialchars($entry['loc']).'</loc>';
        if (! empty($entry['lastmod'])) {
            $xml .= '<lastmod>'.htmlspecialchars($entry['lastmod']).'</lastmod>';
        }
        $xml .= '<changefreq>'.htmlspecialchars($entry['changefreq']).'</changefreq>';
        $xml .= '<priority>'.htmlspecialchars($entry['priority']).'</priority>';
        $xml .= '</url>';
    }
    $xml .= '</urlset>';

    return response($xml, 200)
        ->header('Content-Type', 'application/xml; charset=UTF-8')
        ->header('Cache-Control', 'public, max-age=3600');
})->name('sitemap');

Route::get('/robots.txt', function () {
    $sitemap = route('sitemap');

    return response(
        "User-agent: *\nDisallow: /admin/\nDisallow: /app/\nSitemap: {$sitemap}\n",
        200
    )->header('Content-Type', 'text/plain; charset=UTF-8')
        ->header('Cache-Control', 'public, max-age=3600');
})->name('robots');

// Webhooks (no auth, verified by gateway signature)
Route::middleware('throttle:webhooks')->group(function () {
    Route::post('/webhooks/stripe', [WebhookController::class, 'stripe'])->name('webhooks.stripe');
    Route::post('/webhooks/paypal', [WebhookController::class, 'paypal'])->name('webhooks.paypal');
    Route::post('/webhooks/paddle', [WebhookController::class, 'paddle'])->name('webhooks.paddle');
    Route::post('/webhooks/razorpay', [WebhookController::class, 'razorpay'])->name('webhooks.razorpay');
    Route::post('/webhooks/cashfree', [WebhookController::class, 'cashfree'])->name('webhooks.cashfree');
    Route::post('/webhooks/tap', [WebhookController::class, 'tap'])->name('webhooks.tap');
    Route::post('/webhooks/paystack', [WebhookController::class, 'paystack'])->name('webhooks.paystack');
    Route::post('/webhooks/xendit', [WebhookController::class, 'xendit'])->name('webhooks.xendit');
    Route::post('/webhooks/paymob', [WebhookController::class, 'paymob'])->name('webhooks.paymob');
    Route::post('/webhooks/myfatoorah', [WebhookController::class, 'myfatoorah'])->name('webhooks.myfatoorah');
    Route::post('/webhooks/mollie', [WebhookController::class, 'mollie'])->name('webhooks.mollie');
    Route::post('/webhooks/square', [WebhookController::class, 'square'])->name('webhooks.square');
    Route::post('/webhooks/mercadopago', [WebhookController::class, 'mercadopago'])->name('webhooks.mercadopago');
});

// ─── Health / readiness probes ───────────────────────────────────────────────
// Protected by a shared secret token (HEALTHZ_TOKEN env var). Set to a random
// string in production and pass via Authorization: Bearer <token> header.
Route::middleware('throttle:30,1')->group(function () {
    $guardHealthz = function (Illuminate\Http\Request $request): bool {
        $token = config('app.healthz_token');

        return ! filled($token) || hash_equals($token, $request->bearerToken() ?? '');
    };

    Route::get('/healthz/db', function () use ($guardHealthz) {
        if (! $guardHealthz(request())) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
        try {
            DB::selectOne('SELECT 1');

            return response()->json(['status' => 'ok', 'db' => 'connected']);
        } catch (Throwable $e) {
            return response()->json(['status' => 'error', 'db' => 'database error'], 503);
        }
    })->name('healthz.db');

    Route::get('/healthz/redis', function () use ($guardHealthz) {
        if (! $guardHealthz(request())) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
        try {
            Redis::ping();

            return response()->json(['status' => 'ok', 'redis' => 'connected']);
        } catch (Throwable $e) {
            return response()->json(['status' => 'error', 'redis' => 'redis error'], 503);
        }
    })->name('healthz.redis');

    Route::get('/healthz/queue', function () use ($guardHealthz) {
        if (! $guardHealthz(request())) {
            return response()->json(['error' => 'Unauthorized.'], 401);
        }
        try {
            $size = Queue::size('default');

            return response()->json(['status' => 'ok', 'queue_driver' => config('queue.default'), 'default_size' => $size]);
        } catch (Throwable $e) {
            return response()->json(['status' => 'error', 'queue' => 'queue error'], 503);
        }
    })->name('healthz.queue');
});
