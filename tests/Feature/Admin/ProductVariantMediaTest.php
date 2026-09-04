<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uses literal relative paths (not the route() helper) for request URLs —
 * see AdminCsvXlsxImportTest for why: APP_URL points at a XAMPP
 * subdirectory, which route() bakes into generated URLs but the test HTTP
 * kernel expects app-root-relative paths.
 */
class ProductVariantMediaTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function product(): Product
    {
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category-'.uniqid(),
            'status' => 'active',
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product-'.uniqid(),
            'status' => 'active',
            'is_returnable' => false,
            'return_days' => 7,
        ]);
    }

    private function variant(Product $product, array $overrides = []): ProductVariant
    {
        return ProductVariant::create(array_merge([
            'product_id' => $product->id,
            'variant_name' => 'Test Variant',
            'enable_tiered_pricing' => false,
            'stock_status' => 'in_stock',
            'is_default' => false,
            'status' => 'active',
            'sort_order' => 0,
        ], $overrides));
    }

    private function baseVariantPayload(Product $product): array
    {
        return [
            'product_id' => $product->id,
            'variant_name' => 'Test Variant',
            'enable_tiered_pricing' => '0',
            'stock_status' => 'in_stock',
            'is_default' => '0',
            'status' => 'active',
        ];
    }

    // ---------- upload ----------

    public function test_upload_one_image(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();

        $payload = $this->baseVariantPayload($product);
        $payload['new_media'] = [UploadedFile::fake()->image('one.jpg')];
        $payload['media_order'] = 'n0';

        $this->actingAs($admin)->post('/admin/catalog/variants', $payload)->assertRedirect('/admin/catalog/variants');

        $variant = ProductVariant::first();
        $this->assertSame(1, $variant->images()->count());
        $this->assertSame('image', $variant->images()->first()->media_type);
    }

    public function test_upload_multiple_images(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();

        $payload = $this->baseVariantPayload($product);
        $payload['new_media'] = [
            UploadedFile::fake()->image('a.jpg'),
            UploadedFile::fake()->image('b.png'),
            UploadedFile::fake()->image('c.webp'),
        ];
        $payload['media_order'] = 'n0,n1,n2';

        $this->actingAs($admin)->post('/admin/catalog/variants', $payload)->assertRedirect('/admin/catalog/variants');

        $this->assertSame(3, ProductVariant::first()->images()->count());
    }

    public function test_maximum_ten_images_accepted(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        $files = [];
        $tokens = [];
        for ($i = 0; $i < 10; $i++) {
            $files[] = UploadedFile::fake()->image("img{$i}.jpg");
            $tokens[] = "n{$i}";
        }

        $payload = array_merge($this->baseVariantPayload($product), [
            'new_media' => $files,
            'media_order' => implode(',', $tokens),
        ]);

        $this->actingAs($admin)->put("/admin/catalog/variants/{$variant->id}", $payload)
            ->assertRedirect('/admin/catalog/variants');

        $this->assertSame(10, $variant->fresh()->images()->where('media_type', 'image')->count());
    }

    public function test_eleventh_image_is_rejected(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        // 8 already saved.
        for ($i = 0; $i < 8; $i++) {
            $variant->images()->create([
                'image' => "variants/existing{$i}.jpg",
                'media_type' => 'image',
                'is_primary' => $i === 0,
                'sort_order' => $i + 1,
            ]);
        }

        $files = [UploadedFile::fake()->image('new1.jpg'), UploadedFile::fake()->image('new2.jpg'), UploadedFile::fake()->image('new3.jpg')];

        $payload = array_merge($this->baseVariantPayload($product), [
            'new_media' => $files,
            'media_order' => 'e1,e2,e3,e4,e5,e6,e7,e8,n0,n1,n2',
        ]);

        $response = $this->actingAs($admin)->put("/admin/catalog/variants/{$variant->id}", $payload);

        $response->assertSessionHasErrors('new_media');
        $this->assertSame(8, $variant->fresh()->images()->count());
    }

    public function test_upload_videos(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();

        $payload = $this->baseVariantPayload($product);
        $payload['new_media'] = [UploadedFile::fake()->create('clip.mp4', 500, 'video/mp4')];
        $payload['media_order'] = 'n0';

        $this->actingAs($admin)->post('/admin/catalog/variants', $payload)->assertRedirect('/admin/catalog/variants');

        $variant = ProductVariant::first();
        $this->assertSame('video', $variant->images()->first()->media_type);
    }

    public function test_maximum_five_videos_accepted(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        $files = [];
        $tokens = [];
        for ($i = 0; $i < 5; $i++) {
            $files[] = UploadedFile::fake()->create("clip{$i}.mp4", 500, 'video/mp4');
            $tokens[] = "n{$i}";
        }

        $payload = array_merge($this->baseVariantPayload($product), [
            'new_media' => $files,
            'media_order' => implode(',', $tokens),
        ]);

        $this->actingAs($admin)->put("/admin/catalog/variants/{$variant->id}", $payload)
            ->assertRedirect('/admin/catalog/variants');

        $this->assertSame(5, $variant->fresh()->images()->where('media_type', 'video')->count());
    }

    public function test_sixth_video_is_rejected(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        for ($i = 0; $i < 5; $i++) {
            $variant->images()->create([
                'image' => "variants/existing{$i}.mp4",
                'media_type' => 'video',
                'is_primary' => false,
                'sort_order' => $i + 1,
            ]);
        }

        $payload = array_merge($this->baseVariantPayload($product), [
            'new_media' => [UploadedFile::fake()->create('one-more.mp4', 500, 'video/mp4')],
            'media_order' => 'e1,e2,e3,e4,e5,n0',
        ]);

        $response = $this->actingAs($admin)->put("/admin/catalog/variants/{$variant->id}", $payload);

        $response->assertSessionHasErrors('new_media');
        $this->assertSame(5, $variant->fresh()->images()->count());
    }

    // ---------- format validation ----------

    public function test_invalid_image_type_is_rejected(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();

        // Right extension, wrong actual content type.
        $payload = $this->baseVariantPayload($product);
        $payload['new_media'] = [UploadedFile::fake()->create('fake.jpg', 10, 'text/plain')];
        $payload['media_order'] = 'n0';

        $response = $this->actingAs($admin)->post('/admin/catalog/variants', $payload);

        $response->assertSessionHasErrors('new_media.0');
        $this->assertSame(0, ProductVariant::count());
    }

    public function test_invalid_video_type_is_rejected(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();

        $payload = $this->baseVariantPayload($product);
        $payload['new_media'] = [UploadedFile::fake()->create('fake.mp4', 500, 'application/octet-stream')];
        $payload['media_order'] = 'n0';

        $response = $this->actingAs($admin)->post('/admin/catalog/variants', $payload);

        $response->assertSessionHasErrors('new_media.0');
        $this->assertSame(0, ProductVariant::count());
    }

    public function test_completely_unsupported_extension_is_rejected(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();

        $payload = $this->baseVariantPayload($product);
        $payload['new_media'] = [UploadedFile::fake()->create('document.pdf', 10, 'application/pdf')];
        $payload['media_order'] = 'n0';

        $response = $this->actingAs($admin)->post('/admin/catalog/variants', $payload);

        $response->assertSessionHasErrors('new_media.0');
        $this->assertSame(0, ProductVariant::count());
    }

    // ---------- sorting ----------

    public function test_unified_sort_order_persists_and_is_normalized(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        $imgA = $variant->images()->create(['image' => 'variants/a.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 1]);
        $imgB = $variant->images()->create(['image' => 'variants/b.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 2]);

        // Desired final order: new video, imgA, imgB.
        $payload = array_merge($this->baseVariantPayload($product), [
            'new_media' => [UploadedFile::fake()->create('v.mp4', 500, 'video/mp4')],
            'media_order' => "n0,e{$imgA->id},e{$imgB->id}",
        ]);

        $this->actingAs($admin)->put("/admin/catalog/variants/{$variant->id}", $payload)
            ->assertRedirect('/admin/catalog/variants');

        $ordered = $variant->fresh()->images()->orderBy('sort_order')->get();

        $this->assertSame('video', $ordered[0]->media_type);
        $this->assertSame($imgA->id, $ordered[1]->id);
        $this->assertSame($imgB->id, $ordered[2]->id);
        $this->assertSame([1, 2, 3], $ordered->pluck('sort_order')->all());
    }

    // ---------- primary image ----------

    public function test_primary_image_selection_persists(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        $imgA = $variant->images()->create(['image' => 'variants/a.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 1]);
        $imgB = $variant->images()->create(['image' => 'variants/b.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 2]);

        $payload = array_merge($this->baseVariantPayload($product), [
            'media_order' => "e{$imgA->id},e{$imgB->id}",
            'primary_selector' => "existing:{$imgB->id}",
        ]);

        $this->actingAs($admin)->put("/admin/catalog/variants/{$variant->id}", $payload)
            ->assertRedirect('/admin/catalog/variants');

        $this->assertTrue($imgB->fresh()->is_primary);
        $this->assertFalse($imgA->fresh()->is_primary);
    }

    public function test_new_upload_can_be_selected_as_primary_on_create(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();

        $payload = $this->baseVariantPayload($product);
        $payload['new_media'] = [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')];
        $payload['media_order'] = 'n0,n1';
        $payload['primary_selector'] = 'new:1';

        $this->actingAs($admin)->post('/admin/catalog/variants', $payload)->assertRedirect('/admin/catalog/variants');

        $variant = ProductVariant::first();
        $primary = $variant->images()->where('is_primary', true)->first();
        $this->assertNotNull($primary);
        $this->assertSame(2, $primary->sort_order);
    }

    public function test_video_cannot_be_set_as_primary(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        $video = $variant->images()->create(['image' => 'variants/v.mp4', 'media_type' => 'video', 'is_primary' => false, 'sort_order' => 1]);

        $payload = array_merge($this->baseVariantPayload($product), [
            'media_order' => "e{$video->id}",
            'primary_selector' => "existing:{$video->id}",
        ]);

        $response = $this->actingAs($admin)->put("/admin/catalog/variants/{$variant->id}", $payload);

        $response->assertSessionHasErrors('primary_selector');
        $this->assertFalse($video->fresh()->is_primary);
    }

    public function test_only_one_primary_image_ever_exists(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        $imgA = $variant->images()->create(['image' => 'variants/a.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 1]);
        $imgB = $variant->images()->create(['image' => 'variants/b.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 2]);
        $imgC = $variant->images()->create(['image' => 'variants/c.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 3]);

        $payload = array_merge($this->baseVariantPayload($product), [
            'media_order' => "e{$imgA->id},e{$imgB->id},e{$imgC->id}",
            'primary_selector' => "existing:{$imgC->id}",
        ]);

        $this->actingAs($admin)->put("/admin/catalog/variants/{$variant->id}", $payload);

        $this->assertSame(1, $variant->fresh()->images()->where('is_primary', true)->count());
    }

    public function test_primary_image_is_preserved_when_saving_without_a_primary_selector(): void
    {
        // Regression test: a save that reorders/adds media but omits
        // primary_selector entirely must leave the existing primary image
        // alone, not silently reassign it to whichever image ends up first.
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        $imgA = $variant->images()->create(['image' => 'variants/a.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 1]);
        $imgB = $variant->images()->create(['image' => 'variants/b.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 2]);

        // Reorder so imgA (not the primary) becomes first — primary_selector omitted.
        $payload = array_merge($this->baseVariantPayload($product), [
            'media_order' => "e{$imgA->id},e{$imgB->id}",
        ]);

        $this->actingAs($admin)->put("/admin/catalog/variants/{$variant->id}", $payload)
            ->assertRedirect('/admin/catalog/variants');

        $this->assertTrue($imgB->fresh()->is_primary);
        $this->assertFalse($imgA->fresh()->is_primary);
    }

    public function test_media_order_omitting_an_existing_image_does_not_crash_and_preserves_it(): void
    {
        // Regression test: a media_order that only lists some of the
        // existing images (a malformed/partial request, since the real JS
        // always includes every seeded item) must not crash with a
        // sort_order unique-constraint violation, and must not silently
        // drop the unlisted image.
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        $imgA = $variant->images()->create(['image' => 'variants/a.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 1]);
        $imgB = $variant->images()->create(['image' => 'variants/b.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 2]);

        // media_order only mentions the new file, omitting both existing images.
        $payload = array_merge($this->baseVariantPayload($product), [
            'new_media' => [UploadedFile::fake()->image('c.jpg')],
            'media_order' => 'n0',
        ]);

        $response = $this->actingAs($admin)->put("/admin/catalog/variants/{$variant->id}", $payload);

        $response->assertRedirect('/admin/catalog/variants');
        $this->assertSame(3, $variant->fresh()->images()->count());
        $this->assertSame([1, 2, 3], $variant->fresh()->images()->orderBy('sort_order')->pluck('sort_order')->all());
    }

    // ---------- delete ----------

    public function test_delete_image(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        $image = $variant->images()->create(['image' => 'variants/a.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 1]);

        $this->actingAs($admin)
            ->delete("/admin/catalog/variants/{$variant->id}/images/{$image->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('product_variant_images', ['id' => $image->id]);
    }

    public function test_delete_video(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        $video = $variant->images()->create(['image' => 'variants/v.mp4', 'media_type' => 'video', 'is_primary' => false, 'sort_order' => 1]);

        $this->actingAs($admin)
            ->delete("/admin/catalog/variants/{$variant->id}/images/{$video->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('product_variant_images', ['id' => $video->id]);
    }

    public function test_deleting_primary_promotes_next_image(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        $imgA = $variant->images()->create(['image' => 'variants/a.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 1]);
        $imgB = $variant->images()->create(['image' => 'variants/b.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 2]);

        $this->actingAs($admin)
            ->delete("/admin/catalog/variants/{$variant->id}/images/{$imgA->id}")
            ->assertRedirect();

        $this->assertTrue($imgB->fresh()->is_primary);
    }

    public function test_deleting_primary_never_promotes_a_video(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        $imgA = $variant->images()->create(['image' => 'variants/a.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 1]);
        $video = $variant->images()->create(['image' => 'variants/v.mp4', 'media_type' => 'video', 'is_primary' => false, 'sort_order' => 2]);

        $this->actingAs($admin)
            ->delete("/admin/catalog/variants/{$variant->id}/images/{$imgA->id}")
            ->assertRedirect();

        $this->assertFalse($video->fresh()->is_primary);
        $this->assertSame(0, $variant->fresh()->images()->where('is_primary', true)->count());
    }

    public function test_deleting_media_renormalizes_sort_order(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        $imgA = $variant->images()->create(['image' => 'variants/a.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 1]);
        $imgB = $variant->images()->create(['image' => 'variants/b.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 2]);
        $imgC = $variant->images()->create(['image' => 'variants/c.jpg', 'media_type' => 'image', 'is_primary' => false, 'sort_order' => 3]);

        $this->actingAs($admin)->delete("/admin/catalog/variants/{$variant->id}/images/{$imgB->id}");

        $remaining = $variant->fresh()->images()->orderBy('sort_order')->get();

        $this->assertSame([$imgA->id, $imgC->id], $remaining->pluck('id')->all());
        $this->assertSame([1, 2], $remaining->pluck('sort_order')->all());
    }

    public function test_cannot_delete_media_belonging_to_another_variant(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variantA = $this->variant($product);
        $variantB = $this->variant($product);

        $imageOnB = $variantB->images()->create(['image' => 'variants/b.jpg', 'media_type' => 'image', 'is_primary' => true, 'sort_order' => 1]);

        $this->actingAs($admin)
            ->delete("/admin/catalog/variants/{$variantA->id}/images/{$imageOnB->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('product_variant_images', ['id' => $imageOnB->id]);
    }

    // ---------- backward compatibility ----------

    public function test_existing_legacy_image_records_remain_functional(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();
        $product = $this->product();
        $variant = $this->variant($product);

        // Simulate a pre-migration row: created without ever setting
        // media_type, relying purely on the migration's default.
        $legacyId = DB::table('product_variant_images')->insertGetId([
            'product_variant_id' => $variant->id,
            'image' => 'variants/legacy-flat-path.jpg',
            'is_primary' => true,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $legacy = ProductVariantImage::find($legacyId);

        $this->assertSame('image', $legacy->media_type);
        $this->assertTrue($legacy->isImage());

        $this->actingAs($admin)->get("/admin/catalog/variants/{$variant->id}/edit")->assertOk();
    }
}
