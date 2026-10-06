<?php

namespace Tests\Feature\Storefront;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The `storefront-search` limiter (AppServiceProvider): only requests carrying a search term count, 120 per minute
 * per IP. Normal browsing must never be limited.
 */
class SearchRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_searches_beyond_the_per_minute_limit_get_429_but_normal_volume_is_fine(): void
    {
        for ($i = 1; $i <= 120; $i++) {
            $this->get(route('shop', ['search' => 'soap '.$i]))->assertOk();
        }

        $this->get(route('shop', ['search' => 'one more']))->assertStatus(429);
        // Blog and category search share the same per-IP budget.
        $this->get(route('bloglist', ['search' => 'x']))->assertStatus(429);
    }

    public function test_browsing_without_a_search_term_is_never_limited(): void
    {
        for ($i = 1; $i <= 130; $i++) {
            $this->get(route('shop', ['page' => 1, 'sort' => 'name']))->assertOk();
        }

        $this->get(route('shop'))->assertOk();
    }

    public function test_the_limit_is_per_ip(): void
    {
        for ($i = 1; $i <= 121; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->get(route('shop', ['search' => 'a']));
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])->get(route('shop', ['search' => 'a']))->assertStatus(429);
        $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])->get(route('shop', ['search' => 'a']))->assertOk();
    }
}
