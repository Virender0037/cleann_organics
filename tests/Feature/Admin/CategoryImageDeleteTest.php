<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uses literal relative paths (not the route() helper) for request URLs —
 * see AdminCsvXlsxImportTest for why: APP_URL points at a XAMPP
 * subdirectory, which route() bakes into generated URLs but the test HTTP
 * kernel expects app-root-relative paths.
 */
class CategoryImageDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin']);
    }

    private function categoryWithImage(string $path = 'categories/existing.jpg'): Category
    {
        return Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category-'.uniqid(),
            'status' => 'active',
            'image' => $path,
        ]);
    }

    public function test_category_image_can_be_deleted(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('categories/existing.jpg', 'fake-image-content');
        $admin = $this->superadmin();
        $category = $this->categoryWithImage();

        $response = $this->actingAs($admin)->delete("/admin/catalog/categories/{$category->id}/image");

        $response->assertRedirect();
    }

    public function test_db_image_field_becomes_null(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('categories/existing.jpg', 'fake-image-content');
        $admin = $this->superadmin();
        $category = $this->categoryWithImage();

        $this->actingAs($admin)->delete("/admin/catalog/categories/{$category->id}/image");

        $this->assertNull($category->fresh()->image);
    }

    public function test_physical_file_is_deleted(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('categories/existing.jpg', 'fake-image-content');
        $admin = $this->superadmin();
        $category = $this->categoryWithImage();

        $this->assertTrue(Storage::disk('public')->exists('categories/existing.jpg'));

        $this->actingAs($admin)->delete("/admin/catalog/categories/{$category->id}/image");

        Storage::disk('public')->assertMissing('categories/existing.jpg');
    }

    public function test_deleting_when_physical_file_already_missing_does_not_crash(): void
    {
        Storage::fake('public');
        // Deliberately never put the file on disk — DB row points to a path that doesn't exist.
        $admin = $this->superadmin();
        $category = $this->categoryWithImage('categories/never-actually-uploaded.jpg');

        $response = $this->actingAs($admin)->delete("/admin/catalog/categories/{$category->id}/image");

        $response->assertRedirect();
        $this->assertNull($category->fresh()->image);
    }

    public function test_another_category_image_cannot_be_affected(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('categories/a.jpg', 'a');
        Storage::disk('public')->put('categories/b.jpg', 'b');
        $admin = $this->superadmin();
        $categoryA = $this->categoryWithImage('categories/a.jpg');
        $categoryB = $this->categoryWithImage('categories/b.jpg');

        $this->actingAs($admin)->delete("/admin/catalog/categories/{$categoryA->id}/image");

        $this->assertNull($categoryA->fresh()->image);
        $this->assertSame('categories/b.jpg', $categoryB->fresh()->image);
        Storage::disk('public')->assertExists('categories/b.jpg');
    }

    public function test_category_record_itself_remains_intact(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('categories/existing.jpg', 'fake-image-content');
        $admin = $this->superadmin();
        $category = $this->categoryWithImage();

        $this->actingAs($admin)->delete("/admin/catalog/categories/{$category->id}/image");

        $fresh = $category->fresh();
        $this->assertNotNull($fresh);
        $this->assertSame($category->name, $fresh->name);
        $this->assertSame($category->slug, $fresh->slug);
        $this->assertSame('active', $fresh->status);
    }

    public function test_replacing_image_removes_the_old_image_safely(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('categories/old.jpg', 'old-content');
        $admin = $this->superadmin();
        $category = $this->categoryWithImage('categories/old.jpg');

        $response = $this->actingAs($admin)->put("/admin/catalog/categories/{$category->id}", [
            'name' => $category->name,
            'status' => 'active',
            'image' => UploadedFile::fake()->image('new.jpg'),
        ]);

        $response->assertRedirect('/admin/catalog/categories');

        $fresh = $category->fresh();
        $this->assertNotSame('categories/old.jpg', $fresh->image);
        Storage::disk('public')->assertMissing('categories/old.jpg');
        Storage::disk('public')->assertExists($fresh->image);
    }

    public function test_existing_category_create_and_update_behavior_still_works(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();

        $createResponse = $this->actingAs($admin)->post('/admin/catalog/categories', [
            'name' => 'Brand New Category',
            'status' => 'active',
        ]);
        $createResponse->assertRedirect('/admin/catalog/categories');
        $this->assertDatabaseHas('categories', ['name' => 'Brand New Category']);

        $category = Category::where('name', 'Brand New Category')->first();

        $updateResponse = $this->actingAs($admin)->put("/admin/catalog/categories/{$category->id}", [
            'name' => 'Renamed Category',
            'status' => 'inactive',
        ]);
        $updateResponse->assertRedirect('/admin/catalog/categories');
        $this->assertSame('Renamed Category', $category->fresh()->name);
        $this->assertSame('inactive', $category->fresh()->status);
    }

    public function test_invalid_image_upload_validation_remains_unchanged(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();

        $response = $this->actingAs($admin)->post('/admin/catalog/categories', [
            'name' => 'Bad Image Category',
            'status' => 'active',
            'image' => UploadedFile::fake()->create('not-an-image.pdf', 10, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors('image');
        $this->assertDatabaseMissing('categories', ['name' => 'Bad Image Category']);
    }
}
