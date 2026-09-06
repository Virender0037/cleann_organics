<?php

namespace Tests\Unit\Services\News;

use App\Services\News\EnvironmentalNewsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EnvironmentalNewsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.newsdata.key' => 'test-key']);
        Cache::flush();
    }

    private function successResponse(array $results): array
    {
        return ['status' => 'success', 'results' => $results];
    }

    private function article(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Sustainable packaging cuts plastic waste in half',
            'link' => 'https://example.com/article-1',
            'description' => 'A look at biodegradable packaging adoption.',
            'image_url' => 'https://example.com/image.jpg',
            'pubDate' => '2026-01-01 10:00:00',
            'source_name' => 'Example News',
            'category' => ['environment'],
        ], $overrides);
    }

    public function test_successful_response_is_normalized(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([$this->article()]), 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertCount(1, $articles);
        $article = $articles->first();
        $this->assertSame('Sustainable packaging cuts plastic waste in half', $article->title);
        $this->assertSame('https://example.com/article-1', $article->article_url);
        $this->assertSame('https://example.com/image.jpg', $article->image_url);
        $this->assertSame('Example News', $article->source);
        $this->assertSame('Environment', $article->category);
        $this->assertSame('2026-01-01 10:00:00', $article->published_at->format('Y-m-d H:i:s'));
    }

    public function test_duplicate_article_url_is_removed(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([
                $this->article(['title' => 'First headline']),
                $this->article(['title' => 'Second headline']),
            ]), 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertCount(1, $articles);
        $this->assertSame('First headline', $articles->first()->title);
    }

    public function test_duplicate_title_is_removed(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([
                $this->article(['link' => 'https://example.com/one']),
                $this->article(['link' => 'https://example.com/two']),
            ]), 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertCount(1, $articles);
    }

    public function test_missing_title_is_rejected(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([
                $this->article(['title' => '']),
                $this->article(['title' => 'Valid sustainable headline', 'link' => 'https://example.com/valid']),
            ]), 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertCount(1, $articles);
        $this->assertSame('Valid sustainable headline', $articles->first()->title);
    }

    public function test_missing_url_is_rejected(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([
                $this->article(['link' => null]),
                $this->article(['title' => 'Has a link', 'link' => 'https://example.com/valid']),
            ]), 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertCount(1, $articles);
        $this->assertSame('Has a link', $articles->first()->title);
    }

    public function test_unrelated_article_without_topic_match_is_rejected(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([
                $this->article([
                    'title' => 'Local football team wins championship',
                    'description' => 'A dramatic penalty shootout decided the match in front of a packed stadium.',
                    'category' => null,
                    'link' => 'https://example.com/sports',
                ]),
                $this->article(['title' => 'Organic farming grows nationwide', 'link' => 'https://example.com/organic']),
            ]), 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertCount(1, $articles);
        $this->assertSame('Organic farming grows nationwide', $articles->first()->title);
    }

    public function test_missing_image_falls_back_to_null_instead_of_rejecting_article(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([
                $this->article(['image_url' => null]),
            ]), 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertCount(1, $articles);
        $this->assertNull($articles->first()->image_url);
    }

    public function test_invalid_image_url_falls_back_to_null(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([
                $this->article(['image_url' => 'not-a-url']),
            ]), 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertNull($articles->first()->image_url);
    }

    public function test_category_prefers_meaningful_value_over_newsdatas_generic_top_tag(): void
    {
        // Confirmed against a live NewsData response: the category array
        // routinely leads with a generic "top" priority flag ahead of the
        // actual topical category, e.g. ["top", "environment"].
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([
                $this->article(['category' => ['top', 'environment']]),
            ]), 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertSame('Environment', $articles->first()->category);
    }

    public function test_category_prefers_environment_over_other_secondary_tags(): void
    {
        // e.g. an illegal-plastic-dumping bust NewsData tags
        // ["top", "crime", "environment"] — "environment" is the actual
        // reason this query matched it, so it should win over "crime".
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([
                $this->article(['category' => ['top', 'crime', 'environment']]),
            ]), 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertSame('Environment', $articles->first()->category);
    }

    public function test_category_falls_back_to_top_when_it_is_the_only_value(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([
                $this->article(['category' => ['top']]),
            ]), 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertSame('Top', $articles->first()->category);
    }

    public function test_source_falls_back_to_source_id_when_source_name_missing(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([
                $this->article(['source_name' => null, 'source_id' => 'examplenews']),
            ]), 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertSame('examplenews', $articles->first()->source);
    }

    public function test_published_date_produces_relative_time(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-01-01 12:00:00'));

        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([
                $this->article(['pubDate' => '2026-01-01 09:00:00']),
            ]), 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertSame('3 hours ago', $articles->first()->published_at->diffForHumans());
    }

    public function test_fresh_cache_prevents_additional_api_calls(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([$this->article()]), 200),
        ]);

        $service = app(EnvironmentalNewsService::class);
        $service->latest();
        $service->latest();
        $service->latest();

        Http::assertSentCount(1);
    }

    public function test_refetches_after_fresh_cache_expires(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([$this->article()]), 200),
        ]);

        $service = app(EnvironmentalNewsService::class);
        $service->latest();

        $this->travel(25)->hours();

        $service->latest();

        Http::assertSentCount(2);
    }

    public function test_failed_refetch_after_expiry_serves_stale_cache(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response($this->successResponse([
                $this->article(['title' => 'Original stale article']),
            ]), 200),
        ]);

        $service = app(EnvironmentalNewsService::class);
        $first = $service->latest();
        $this->assertSame('Original stale article', $first->first()->title);

        $this->travel(25)->hours();

        Http::fake([
            'newsdata.io/*' => Http::response([], 500),
        ]);

        $second = $service->latest();

        $this->assertCount(1, $second);
        $this->assertSame('Original stale article', $second->first()->title);
    }

    public function test_first_ever_api_failure_returns_empty_collection_not_an_exception(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response([], 500),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertCount(0, $articles);
    }

    public function test_malformed_json_does_not_throw(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response('not json at all', 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertCount(0, $articles);
    }

    public function test_unexpected_response_shape_does_not_throw(): void
    {
        Http::fake([
            'newsdata.io/*' => Http::response(['status' => 'success', 'results' => 'not-an-array'], 200),
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertCount(0, $articles);
    }

    public function test_timeout_does_not_throw(): void
    {
        Http::fake([
            'newsdata.io/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
            },
        ]);

        $articles = app(EnvironmentalNewsService::class)->latest();

        $this->assertCount(0, $articles);
    }
}
