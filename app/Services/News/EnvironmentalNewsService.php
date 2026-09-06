<?php

namespace App\Services\News;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Real environmental/organic/sustainability news for the homepage's
 * "Latest News" slider, sourced from NewsData.io.
 *
 * This service only ever returns genuine articles or an empty collection —
 * it never fabricates data and never throws. Whether to fall back to the
 * old static demo cards when it returns empty is a presentation decision
 * left to the view (see home.blade.php's "news" section), not something
 * this service pretends is real news.
 */
class EnvironmentalNewsService
{
    private const ENDPOINT = 'https://newsdata.io/api/1/latest';

    /**
     * No expiry — overwritten only by another successful fetch, and read
     * back only when both the fresh cache has expired AND a live refetch
     * has just failed. This is what lets a NewsData outage serve yesterday's
     * real articles instead of an empty section.
     */
    private const FRESH_KEY = 'news:environmental:fresh';

    private const STALE_KEY = 'news:environmental:stale';

    private const FRESH_TTL_HOURS = 24;

    /**
     * Safety net beyond the API's own category/query scoping: an article is
     * kept only if its title/description actually mentions one of these, or
     * it already carries a genuinely-returned "environment"-ish category.
     */
    private const TOPIC_KEYWORDS = [
        'environment', 'environmental', 'sustainab', 'organic',
        'biodegradable', 'eco-friendly', 'eco friendly', 'ecofriendly',
        'zero waste', 'zero-waste', 'green living', 'climate', 'renewable',
        'recycl', 'compost', 'carbon',
    ];

    public function latest(int $limit = 9): Collection
    {
        $fresh = Cache::get(self::FRESH_KEY);

        if ($fresh !== null) {
            return $fresh;
        }

        try {
            $articles = $this->fetch($limit);

            Cache::put(self::FRESH_KEY, $articles, now()->addHours(self::FRESH_TTL_HOURS));
            Cache::forever(self::STALE_KEY, $articles);

            return $articles;
        } catch (Throwable $e) {
            Log::warning('EnvironmentalNewsService: falling back to stale/empty — '.$e->getMessage());

            return Cache::get(self::STALE_KEY) ?? collect();
        }
    }

    private function fetch(int $limit): Collection
    {
        $apiKey = config('services.newsdata.key');

        if (blank($apiKey)) {
            throw new RuntimeException('NEWSDATA_API_KEY is not configured.');
        }

        $response = Http::timeout(5)->get(self::ENDPOINT, [
            'apikey' => $apiKey,
            'language' => 'en',
            'category' => 'environment',
            'image' => 1,
            'q' => 'organic OR sustainable OR "eco-friendly" OR biodegradable OR "zero waste"',
        ]);

        if (! $response->successful()) {
            throw new RuntimeException("NewsData responded with HTTP {$response->status()}.");
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('NewsData returned malformed JSON.');
        }

        if (($payload['status'] ?? null) !== 'success' || ! is_array($payload['results'] ?? null)) {
            throw new RuntimeException('NewsData returned an unexpected response shape.');
        }

        return $this->normalize($payload['results'], $limit);
    }

    /**
     * @param  array<int, array<string, mixed>>  $results
     */
    private function normalize(array $results, int $limit): Collection
    {
        $seenUrls = [];
        $seenTitles = [];

        return collect($results)
            ->map(fn (array $item) => $this->mapArticle($item))
            ->filter(fn (?array $article) => $article !== null)
            ->filter(function (array $article) use (&$seenUrls, &$seenTitles) {
                $urlKey = strtolower($article['article_url']);
                $titleKey = strtolower(trim($article['title']));

                if (isset($seenUrls[$urlKey]) || isset($seenTitles[$titleKey])) {
                    return false;
                }

                $seenUrls[$urlKey] = true;
                $seenTitles[$titleKey] = true;

                return true;
            })
            ->filter(fn (array $article) => $this->matchesTopic($article))
            ->take($limit)
            ->values()
            ->map(fn (array $article) => (object) $article);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>|null null when the article is unusable
     *                                    (no title, no valid link).
     */
    private function mapArticle(array $item): ?array
    {
        // NewsData's field names have shifted across API versions; read
        // defensively rather than assume one name (see the audit's query
        // validation note — confirm the live shape once a real key runs,
        // these fallbacks cover the documented current + prior names).
        $title = trim((string) ($item['title'] ?? ''));
        $url = $item['link'] ?? $item['article_url'] ?? $item['url'] ?? null;

        if ($title === '' || blank($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $imageUrl = $item['image_url'] ?? $item['image'] ?? null;
        if (! is_string($imageUrl) || $imageUrl === '' || ! filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $imageUrl = null;
        }

        $publishedAt = null;
        $rawDate = $item['pubDate'] ?? $item['published_at'] ?? $item['publishedAt'] ?? null;
        if (filled($rawDate)) {
            try {
                $publishedAt = Carbon::parse($rawDate);
            } catch (Throwable) {
                $publishedAt = null;
            }
        }

        // Confirmed against a live response: NewsData's `category` array
        // routinely carries several tags at once (e.g. ["top", "crime",
        // "environment"]) — "top" is just its generic priority flag, and a
        // secondary tag like "crime" (e.g. an illegal-plastic-dumping bust)
        // can rank ahead of "environment" despite that being the actual
        // reason this query matched the article at all. Prefer "environment"
        // whenever it's genuinely present; otherwise the first non-"top"
        // value; otherwise fall back to index 0.
        $category = null;
        $rawCategory = $item['category'] ?? null;
        if (is_array($rawCategory)) {
            $values = collect($rawCategory)->filter(fn ($value) => is_string($value) && $value !== '');

            $chosen = $values->first(fn ($value) => strtolower($value) === 'environment')
                ?? $values->first(fn ($value) => strtolower($value) !== 'top')
                ?? $values->first();

            $category = filled($chosen) ? ucfirst((string) $chosen) : null;
        } elseif (is_string($rawCategory) && $rawCategory !== '') {
            $category = ucfirst($rawCategory);
        }

        $source = $item['source_name'] ?? $item['source_id'] ?? $item['source'] ?? null;

        return [
            'title' => $title,
            'description' => filled($item['description'] ?? null) ? trim((string) $item['description']) : null,
            'image_url' => $imageUrl,
            'article_url' => $url,
            'source' => filled($source) ? (string) $source : null,
            'category' => $category,
            'published_at' => $publishedAt,
        ];
    }

    /**
     * @param  array<string, mixed>  $article
     */
    private function matchesTopic(array $article): bool
    {
        if (filled($article['category'] ?? null)) {
            return true;
        }

        $haystack = strtolower($article['title'].' '.($article['description'] ?? ''));

        foreach (self::TOPIC_KEYWORDS as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
