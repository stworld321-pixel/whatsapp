<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Services\StorageManager;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('marketing/BlogIndex', [
            'posts' => BlogPost::where('published', true)
                ->latest()
                ->get()
                ->map(fn (BlogPost $post) => [
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'category' => $post->category,
                    'meta_title' => $post->meta_title,
                    'meta_description' => $post->meta_description,
                    'image_url' => $this->imageUrl($post),
                    'created_at' => $post->created_at?->toDateString(),
                ]),
        ]);
    }

    public function show(string $slug): Response
    {
        $post = BlogPost::where('slug', $slug)
            ->where('published', true)
            ->firstOrFail();

        return Inertia::render('marketing/BlogShow', [
            'post' => [
                'title' => $post->title,
                'slug' => $post->slug,
                'content' => $post->content,
                'meta_title' => $post->meta_title,
                'meta_description' => $post->meta_description,
                'category' => $post->category,
                'image_url' => $this->imageUrl($post),
                'created_at' => $post->created_at?->toDateString(),
            ],
        ]);
    }

    public function image(string $slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();
        if (! $post->image_path) {
            abort(404);
        }

        $disk = $post->image_disk ?: 'public';
        app(StorageManager::class)->ensureDiskReady($disk);

        if (! Storage::disk($disk)->exists($post->image_path)) {
            abort(404);
        }

        return Storage::disk($disk)->response($post->image_path, null, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function imageUrl(BlogPost $post): ?string
    {
        return $post->image_path ? route('blog.image', ['slug' => $post->slug, 'v' => $post->updated_at?->timestamp ?? time()]) : null;
    }
}
