<?php

namespace Tests\Feature\Storefront;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Literal relative "/" URL (not route()) — see HomeTest for why: APP_URL
 * points at a XAMPP subdirectory in this env.
 */
class HomeNewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.newsdata.key' => 'test-key']);
        Cache::flush();
    }

    private function successResponse(array $overrides = []): array
    {
        return [
            'status' => 'success',
            'results' => [array_merge([
                'title' => 'Zero-waste living gains popularity in cities',
                'link' => 'https://example.com/zero-waste-cities',
                'description' => 'A report on zero-waste and sustainable urban living.',
                'image_url' => 'https://example.com/zero-waste.jpg',
                'pubDate' => now()->subHours(3)->format('Y-m-d H:i:s'),
                'source_name' => 'Example Green News',
                'category' => ['environment'],
            ], $overrides)],
        ];
    }

    public function test_homepage_renders_real_news_article(): void
    {
        Http::fake(['newsdata.io/*' => Http::response($this->successResponse(), 200)]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Zero-waste living gains popularity in cities');
        $response->assertSee('Example Green News');
    }

    public function test_external_article_link_opens_in_new_tab_with_safe_rel(): void
    {
        Http::fake(['newsdata.io/*' => Http::response($this->successResponse(), 200)]);

        $response = $this->get('/');

        $response->assertOk();
        $content = $response->getContent();

        $this->assertStringContainsString('href="https://example.com/zero-waste-cities" class="blog-title', $content);
        $this->assertStringContainsString('target="_blank" rel="noopener noreferrer"', $content);
    }

    public function test_real_image_and_relative_time_are_rendered(): void
    {
        Http::fake(['newsdata.io/*' => Http::response($this->successResponse(), 200)]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('https://example.com/zero-waste.jpg', false);
        $response->assertSee('3 hours ago');
    }

    public function test_first_ever_api_failure_falls_back_to_static_cards_not_an_empty_section(): void
    {
        Http::fake(['newsdata.io/*' => Http::response([], 500)]);

        $response = $this->get('/');

        $response->assertOk();
        // The original static demo card content — used only as the
        // emergency fallback when neither a fresh nor stale cache exists.
        $response->assertSee('Curabitur porttitor orci eget neque accumsan venenatis');
    }

    public function test_malformed_api_response_does_not_break_homepage(): void
    {
        Http::fake(['newsdata.io/*' => Http::response('not json', 200)]);

        $this->get('/')->assertOk();
    }

    public function test_stale_cache_is_preferred_over_static_fallback_on_api_failure(): void
    {
        Http::fake(['newsdata.io/*' => Http::response($this->successResponse(), 200)]);
        $this->get('/'); // populates fresh + stale cache

        $this->travel(25)->hours();

        Http::fake(['newsdata.io/*' => Http::response([], 500)]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Zero-waste living gains popularity in cities');
        $response->assertDontSee('Curabitur porttitor orci eget neque accumsan venenatis');
    }

    public function test_news_section_does_not_affect_unrelated_homepage_sections(): void
    {
        Http::fake(['newsdata.io/*' => Http::response($this->successResponse(), 200)]);

        $category = \App\Models\Category::create([
            'name' => 'News Regression Category',
            'slug' => 'news-regression-category',
            'status' => 'active',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('News Regression Category');
        $response->assertSee('Popular Categories');
        $response->assertSee('Popular products');
        $response->assertSee('Hot Deals');
        $response->assertSee('Featured Products');
    }
}
