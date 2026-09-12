<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Dev/demo content for the now database-driven /bloglist and /singleblog/{slug}
 * pages. Safe to re-run — categories/tags/posts are matched by slug and
 * updated in place rather than duplicated. One post is left as 'draft' on
 * purpose, to make the published-only storefront filter visibly provable
 * against real seeded data, not just factory-made test rows.
 */
class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('role', 'superadmin')->first();

        $categories = [
            'natural-cleaning' => 'Natural Cleaning',
            'ayurvedic-wellness' => 'Ayurvedic Wellness',
            'zero-waste-living' => 'Zero-Waste Living',
        ];

        $categoryModels = [];
        foreach ($categories as $slug => $name) {
            $categoryModels[$slug] = BlogCategory::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'status' => 'active', 'sort_order' => 0]
            );
        }

        $tags = ['Bio-Enzyme', 'Chemical-Free', 'Copper Water', 'Plastic-Free', 'Home Care'];
        $tagModels = [];
        foreach ($tags as $name) {
            $tagModels[$name] = BlogTag::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'status' => 'active']
            );
        }

        foreach ($this->posts() as $post) {
            $blog = Blog::updateOrCreate(
                ['slug' => $post['slug']],
                [
                    'blog_category_id' => $categoryModels[$post['category']]->id,
                    'user_id' => $author?->id,
                    'title' => $post['title'],
                    'short_description' => $post['short_description'],
                    'content' => $post['content'],
                    'status' => $post['status'],
                    'published_at' => $post['published_at'],
                    'is_featured' => $post['is_featured'],
                    'view_count' => $post['view_count'],
                ]
            );

            $blog->tags()->sync(collect($post['tags'])->map(fn ($name) => $tagModels[$name]->id));
        }
    }

    /**
     * @return array<int, array{slug:string, title:string, category:string, tags:array<int,string>, short_description:string, content:string, status:string, published_at:?Carbon, is_featured:bool, view_count:int}>
     */
    private function posts(): array
    {
        return [
            [
                'slug' => 'why-bio-enzyme-cleaners-are-better-for-your-home',
                'title' => 'Why Bio-Enzyme Cleaners Are Better for Your Home',
                'category' => 'natural-cleaning',
                'tags' => ['Bio-Enzyme', 'Chemical-Free', 'Home Care'],
                'short_description' => 'Bio-enzyme cleaners break down grime naturally, without the harsh fumes of conventional floor cleaners. Here is how they work and why we make ours the way we do.',
                'content' => '<p>Most floor cleaners on supermarket shelves rely on synthetic surfactants and strong fragrances to feel "clean." Bio-enzyme cleaners work differently: live enzymes and beneficial microbes actually digest grease, food residue, and organic grime, rather than just masking it.</p><p>The result is a floor that is genuinely sanitised, not just perfumed — with no harsh chemical residue left behind for children or pets to come into contact with. Dilute, mop, and let the enzymes do the rest.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(21),
                'is_featured' => true,
                'view_count' => 184,
            ],
            [
                'slug' => 'the-ayurvedic-case-for-drinking-from-copper',
                'title' => 'The Ayurvedic Case for Drinking from Copper',
                'category' => 'ayurvedic-wellness',
                'tags' => ['Copper Water', 'Chemical-Free'],
                'short_description' => 'Ayurveda has recommended storing water in copper vessels for centuries. We look at the tradition, the practical benefits, and how to build the habit.',
                'content' => '<p>Ayurvedic tradition calls water stored overnight in a copper vessel "tamra jal" — believed to help balance all three doshas. Copper is also naturally antimicrobial, which is part of why the practice has persisted for generations.</p><p>To build the habit: fill a copper bottle before bed, and drink it first thing in the morning on an empty stomach. Avoid hot liquids or citrus directly in copper, since acidity accelerates the natural patina.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(14),
                'is_featured' => true,
                'view_count' => 231,
            ],
            [
                'slug' => 'five-easy-swaps-to-start-a-zero-waste-kitchen',
                'title' => 'Five Easy Swaps to Start a Zero-Waste Kitchen',
                'category' => 'zero-waste-living',
                'tags' => ['Plastic-Free', 'Home Care'],
                'short_description' => 'You do not need to overhaul your kitchen overnight. Start with these five simple, low-cost swaps away from single-use plastic.',
                'content' => '<p>Zero-waste living can feel overwhelming if you try to change everything at once. Instead, start small: coconut coir scrubbers instead of synthetic sponges, bamboo brushes instead of plastic ones, and refillable bottles instead of single-use packaging.</p><p>Each swap on its own is small. Together, over a year, they meaningfully cut the plastic your household sends to landfill — without requiring a lifestyle overhaul.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(7),
                'is_featured' => false,
                'view_count' => 97,
            ],
            [
                'slug' => 'reading-your-cleaning-product-label-what-to-avoid',
                'title' => 'Reading Your Cleaning Product Label: What to Avoid',
                'category' => 'natural-cleaning',
                'tags' => ['Chemical-Free', 'Home Care'],
                'short_description' => 'Not everything labelled "natural" actually is. Here is what to look for — and what to avoid — the next time you check a cleaning product label.',
                'content' => '<p>Terms like "natural" and "eco-friendly" are not regulated the way you might expect, so the label alone is not proof of anything. Look instead for a full ingredient list, and watch for common irritants like sodium lauryl sulfate, synthetic fragrance blends, and chlorine-based bleaches.</p><p>A genuinely natural cleaner should be comfortable disclosing exactly what is in the bottle — enzymes, plant extracts, and essential oils, by name.</p>',
                'status' => 'published',
                'published_at' => now()->subDays(2),
                'is_featured' => false,
                'view_count' => 42,
            ],
            [
                'slug' => 'building-a-morning-ayurvedic-routine-that-actually-sticks',
                'title' => 'Building a Morning Ayurvedic Routine That Actually Sticks',
                'category' => 'ayurvedic-wellness',
                'tags' => ['Copper Water', 'Chemical-Free'],
                'short_description' => 'Small, consistent habits beat an elaborate routine you abandon after a week. Here is a realistic starting point.',
                'content' => '<p>The easiest way to build a lasting Ayurvedic morning routine is to anchor it to something you already do — like drinking water first thing, or brushing your teeth. Add copper-water first, then a natural tooth powder, before reaching for anything else.</p><p>Consistency for two weeks matters far more than intensity on day one.</p>',
                'status' => 'published',
                'published_at' => now()->subDay(),
                'is_featured' => false,
                'view_count' => 15,
            ],
            [
                'slug' => 'upcoming-diwali-gifting-guide-draft',
                'title' => '[Draft] Diwali Gifting Guide — Not Yet Ready',
                'category' => 'zero-waste-living',
                'tags' => ['Plastic-Free'],
                'short_description' => 'Work in progress — do not publish until final product list is confirmed.',
                'content' => '<p>Draft content pending marketing review.</p>',
                'status' => 'draft',
                'published_at' => null,
                'is_featured' => false,
                'view_count' => 0,
            ],
        ];
    }
}
