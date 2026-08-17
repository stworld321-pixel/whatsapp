<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\CmsPage;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_is_visible_by_default(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_disabled_landing_page_redirects_to_login(): void
    {
        SystemSetting::set('landing.page_enabled', '0', false, 'landing');

        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_disabled_landing_hides_marketing_pages(): void
    {
        SystemSetting::set('landing.page_enabled', '0', false, 'landing');

        foreach (['/pricing', '/faq', '/use-cases'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_admin_can_disable_landing_page(): void
    {
        $this->actingAs($this->createSuperAdmin(), 'admin')
            ->put(route('admin.landing-page.update'), [
                'settings' => ['landing.page_enabled' => '0'],
            ])
            ->assertRedirect();

        $this->assertSame('0', SystemSetting::get('landing.page_enabled', '1'));

        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_sitemap_excludes_marketing_pages_when_disabled(): void
    {
        SystemSetting::set('landing.page_enabled', '0', false, 'landing');

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee('/pricing');
    }

    public function test_sitemap_and_robots_expose_public_seo_urls(): void
    {
        CmsPage::factory()->create([
            'slug' => 'privacy-policy',
            'title' => 'Privacy Policy',
            'published' => true,
        ]);

        BlogPost::create([
            'slug' => 'seo-friendly-launch',
            'title' => 'SEO Friendly Launch',
            'content' => '<p>Launch content</p>',
            'meta_title' => 'SEO Friendly Launch',
            'meta_description' => 'Launch content for search engines.',
            'category' => 'Marketing',
            'published' => true,
        ]);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('User-agent: *')
            ->assertSee('Disallow: /admin/')
            ->assertSee(route('sitemap'));

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(url('/'))
            ->assertSee(url('/pricing'))
            ->assertSee(route('cms-page.show', 'privacy-policy'))
            ->assertSee(route('blog.show', 'seo-friendly-launch'))
            ->assertSeeHtml('<lastmod>')
            ->assertSeeHtml('<changefreq>')
            ->assertSeeHtml('<priority>');
    }
}
