<?php

namespace App\Support;

/**
 * Read-only access to config/marketplaces.php. Views and code ask this class about marketplaces —
 * nothing else in the app should ever branch on "amazon"/"flipkart"/"meesho".
 */
class Marketplaces
{
    /** @var array<string, string|null> */
    private static array $logoCache = [];

    /** @return array<string, array<string, mixed>> every configured marketplace, enabled or not */
    public static function all(): array
    {
        return (array) config('marketplaces.list', []);
    }

    /** @return array<string, string> key => label for the marketplaces that may be used */
    public static function options(): array
    {
        $options = [];

        foreach (self::all() as $key => $definition) {
            if ($definition['enabled'] ?? true) {
                $options[$key] = (string) $definition['label'];
            }
        }

        return $options;
    }

    public static function exists(string $key): bool
    {
        return array_key_exists($key, self::all());
    }

    public static function isEnabled(string $key): bool
    {
        return (bool) (self::all()[$key]['enabled'] ?? false);
    }

    public static function label(string $key): string
    {
        return (string) (self::all()[$key]['label'] ?? ucfirst($key));
    }

    /** @return array<int, string> */
    public static function domains(string $key): array
    {
        return array_values((array) (self::all()[$key]['domains'] ?? []));
    }

    /**
     * URL of an uploaded logo file (public/images/marketplaces/{key}.svg|png|webp|jpg), or null when none
     * exists — the caller then draws the text wordmark. Local files only; nothing is ever hotlinked.
     */
    public static function logoUrl(string $key): ?string
    {
        if (array_key_exists($key, self::$logoCache)) {
            return self::$logoCache[$key];
        }

        $found = null;

        foreach (['svg', 'png', 'webp', 'jpg', 'jpeg'] as $extension) {
            if (is_file(public_path("images/marketplaces/{$key}.{$extension}"))) {
                $found = asset("images/marketplaces/{$key}.{$extension}");
                break;
            }
        }

        return self::$logoCache[$key] = $found;
    }

    public static function flushLogoCache(): void
    {
        self::$logoCache = [];
    }

    /** @return array<int, string> */
    public static function clickSources(): array
    {
        return (array) config('marketplaces.click_sources', []);
    }

    public static function staleAfterDays(): int
    {
        return (int) config('marketplaces.stale_after_days', 30);
    }

    /** Whether $url's host is (a subdomain of) one of the marketplace's known domains. */
    public static function urlBelongsTo(string $url, string $key): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = (string) preg_replace('/^www\./', '', $host);

        foreach (self::domains($key) as $domain) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return true;
            }
        }

        return false;
    }
}
