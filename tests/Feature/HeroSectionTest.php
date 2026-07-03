<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use SGCart\Hero\Models\HeroImage;
use Tests\TestCase;

class HeroSectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permission for tests
        if (class_exists(\Spatie\Permission\Models\Permission::class)) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage hero section', 'guard_name' => 'web']);
        }
    }

    /**
     * Test admin can view hero section settings.
     */
    public function test_admin_can_view_hero_settings(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);
        if (class_exists(\Spatie\Permission\Models\Permission::class)) {
            $admin->givePermissionTo('manage hero section');
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.hero.settings'));

        $response->assertStatus(200);
        $response->assertSee('Hero Section Settings');
        $response->assertSee('Upload Slides');
    }

    /**
     * Test admin can save settings and upload multiple images.
     */
    public function test_admin_can_update_hero_settings_and_upload_images(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);
        if (class_exists(\Spatie\Permission\Models\Permission::class)) {
            $admin->givePermissionTo('manage hero section');
        }

        $file1 = UploadedFile::fake()->image('slide1.jpg');
        $file2 = UploadedFile::fake()->image('slide2.jpg');

        $response = $this->actingAs($admin)
            ->post(route('admin.hero.settings.update'), [
                'images' => [$file1, $file2],
                'sort_order' => [],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert DB has images
        $this->assertEquals(2, HeroImage::count());

        // Assert files are stored
        $images = HeroImage::all();
        foreach ($images as $img) {
            Storage::disk('public')->assertExists($img->image_path);
        }
    }

    /**
     * Test admin can delete an uploaded image.
     */
    public function test_admin_can_delete_uploaded_image(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);
        if (class_exists(\Spatie\Permission\Models\Permission::class)) {
            $admin->givePermissionTo('manage hero section');
        }

        // Pre-create slide
        $path = Storage::disk('public')->putFile('hero', UploadedFile::fake()->image('slide1.jpg'));
        $slide = HeroImage::create([
            'image_path' => $path,
            'sort_order' => 0,
        ]);

        Storage::disk('public')->assertExists($path);

        $response = $this->actingAs($admin)
            ->delete(route('admin.hero.settings.delete-image', $slide->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert record is deleted from DB
        $this->assertEquals(0, HeroImage::count());

        // Assert file is deleted from disk
        Storage::disk('public')->assertMissing($path);
    }

    /**
     * Test admin can bulk delete uploaded images.
     */
    public function test_admin_can_bulk_delete_uploaded_images(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);
        if (class_exists(\Spatie\Permission\Models\Permission::class)) {
            $admin->givePermissionTo('manage hero section');
        }

        // Pre-create slide 1 & 2
        $path1 = Storage::disk('public')->putFile('hero', UploadedFile::fake()->image('slide1.jpg'));
        $slide1 = HeroImage::create([
            'image_path' => $path1,
            'sort_order' => 0,
        ]);
        $path2 = Storage::disk('public')->putFile('hero', UploadedFile::fake()->image('slide2.jpg'));
        $slide2 = HeroImage::create([
            'image_path' => $path2,
            'sort_order' => 1,
        ]);

        Storage::disk('public')->assertExists($path1);
        Storage::disk('public')->assertExists($path2);

        $response = $this->actingAs($admin)
            ->delete(route('admin.hero.settings.bulk-delete'), [
                'ids' => [$slide1->id, $slide2->id]
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert record is deleted from DB
        $this->assertEquals(0, HeroImage::count());

        // Assert file is deleted from disk
        Storage::disk('public')->assertMissing($path1);
        Storage::disk('public')->assertMissing($path2);
    }

    /**
     * Test storefront home page variables load.
     */
    public function test_home_page_loads_hero_settings_variables(): void
    {
        // Setup slide
        HeroImage::create(['image_path' => 'hero/slide.jpg', 'sort_order' => 1]);

        $response = $this->get(route('store.home'));

        $response->assertStatus(200);
        $response->assertViewHas('heroImages');
    }
}
