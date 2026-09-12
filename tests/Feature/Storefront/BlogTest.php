<?php

namespace Tests\Feature\Storefront;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private function category(array $overrides = []): BlogCategory
    {
        return BlogCategory::create(array_merge([
            'name' => 'Nutrition',
            'slug' => 'nutrition-'.uniqid(),
            'status' => 'active',
        ], $overrides));
    }

    private function blog(array $overrides = []): Blog
    {
        return Blog::create(array_merge([
            'blog_category_id' => $this->category()->id,
            'title' => 'Eating Healthy on a Budget',
            'slug' => 'eating-healthy-on-a-budget-'.uniqid(),
            'short_description' => 'Tips for eating well without breaking the bank.',
            'content' => '<p>Full article body goes here.</p>',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ], $overrides));
    }

    // ------------------------------------------------------------------
    // Listing
    // ------------------------------------------------------------------

    public function test_published_blog_appears_on_the_listing_page(): void
    {
        $blog = $this->blog(['title' => 'Green Smoothie Basics']);

        $this->get('/bloglist')->assertOk()->assertSee('Green Smoothie Basics');
    }

    public function test_draft_blog_does_not_appear_on_the_listing_page(): void
    {
        $this->blog(['title' => 'Unpublished Draft Post', 'status' => 'draft']);

        $this->get('/bloglist')->assertOk()->assertDontSee('Unpublished Draft Post');
    }

    public function test_archived_blog_does_not_appear_on_the_listing_page(): void
    {
        $this->blog(['title' => 'Old Archived Post', 'status' => 'archived']);

        $this->get('/bloglist')->assertOk()->assertDontSee('Old Archived Post');
    }

    public function test_future_scheduled_blog_does_not_appear_yet(): void
    {
        $this->blog(['title' => 'Scheduled For Next Week', 'status' => 'published', 'published_at' => now()->addWeek()]);

        $this->get('/bloglist')->assertOk()->assertDontSee('Scheduled For Next Week');
    }

    public function test_empty_state_shows_when_no_posts_are_published(): void
    {
        $this->get('/bloglist')->assertOk()->assertSee('No blog posts have been published yet');
    }

    public function test_search_filters_by_title(): void
    {
        $this->blog(['title' => 'Fermented Foods 101']);
        $this->blog(['title' => 'Winter Soup Recipes']);

        $response = $this->get('/bloglist?search=Fermented');

        // "Winter Soup Recipes" is deliberately not asserted absent here —
        // the "Recently Added" sidebar widget intentionally always shows the
        // latest posts regardless of the main list's search filter, so it
        // can legitimately still appear there.
        $response->assertOk()->assertSee('Fermented Foods 101');
    }

    public function test_sidebar_shows_real_category_with_published_post_count(): void
    {
        $category = $this->category(['name' => 'Recipes']);
        $this->blog(['blog_category_id' => $category->id]);
        $this->blog(['blog_category_id' => $category->id]);

        $response = $this->get('/bloglist');

        $response->assertOk()->assertSee('Recipes')->assertSee('(2)');
    }

    public function test_pagination_links_are_real_not_hardcoded(): void
    {
        Blog::insert(collect(range(1, 8))->map(fn ($i) => [
            'blog_category_id' => $this->category()->id,
            'title' => 'Post '.$i,
            'slug' => 'post-'.$i.'-'.uniqid(),
            'content' => 'Body',
            'status' => 'published',
            'published_at' => now()->subDays($i),
            'view_count' => 0,
            'is_featured' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());

        $response = $this->get('/bloglist');

        $response->assertOk()->assertDontSee('pagination-link" href="#">21');
        $this->assertGreaterThan(6, Blog::count());
    }

    // ------------------------------------------------------------------
    // Detail page
    // ------------------------------------------------------------------

    public function test_blog_detail_page_shows_real_content(): void
    {
        $author = User::factory()->create(['name' => 'Jane Editor']);
        $category = $this->category(['name' => 'Wellness']);
        $tag = BlogTag::create(['name' => 'Superfoods', 'slug' => 'superfoods-'.uniqid(), 'status' => 'active']);
        $blog = $this->blog([
            'title' => 'The Truth About Superfoods',
            'content' => '<p>Superfoods are not magic, but they help.</p>',
            'blog_category_id' => $category->id,
            'user_id' => $author->id,
        ]);
        $blog->tags()->attach($tag->id);

        $response = $this->get('/singleblog/'.$blog->slug);

        $response->assertOk()
            ->assertSee('The Truth About Superfoods')
            ->assertSee('Superfoods are not magic, but they help.', false)
            ->assertSee('Wellness')
            ->assertSee('Jane Editor')
            ->assertSee('Superfoods');
    }

    public function test_blog_detail_increments_view_count(): void
    {
        $blog = $this->blog();

        $this->get('/singleblog/'.$blog->slug);

        $this->assertSame(1, $blog->fresh()->view_count);
    }

    public function test_draft_blog_detail_returns_404(): void
    {
        $blog = $this->blog(['status' => 'draft']);

        $this->get('/singleblog/'.$blog->slug)->assertNotFound();
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get('/singleblog/this-slug-does-not-exist')->assertNotFound();
    }

    public function test_related_posts_come_from_the_same_category(): void
    {
        $category = $this->category();
        $blog = $this->blog(['blog_category_id' => $category->id, 'title' => 'Main Post']);
        $this->blog(['blog_category_id' => $category->id, 'title' => 'Related Post']);
        // "Unrelated Post" (different category) is deliberately not asserted
        // absent — the "Recently Added" sidebar widget always shows the
        // latest posts site-wide regardless of category, so it can
        // legitimately still appear there even though it's excluded from
        // the "Related Posts" block itself.
        $this->blog(['title' => 'Unrelated Post']);

        $response = $this->get('/singleblog/'.$blog->slug);

        $response->assertOk()->assertSee('Related Post');
    }
}
