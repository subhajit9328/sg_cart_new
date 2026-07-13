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
        $response->assertSee('Slide Management');
        $response->assertSee('Add Slide');
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
                'new_urls' => ['https://google.com', 'https://yahoo.com'],
                'sort_order' => [],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert DB has images and URLs saved correctly
        $this->assertEquals(2, HeroImage::count());
        $images = HeroImage::orderBy('id')->get();
        $this->assertEquals('https://google.com', $images[0]->url);
        $this->assertEquals('https://yahoo.com', $images[1]->url);

        // Assert files are stored
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
     * Test uploading images fails when exceeding configured maximum limit.
     */
    public function test_uploading_images_fails_exceeding_max_limit(): void
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

        // Mock max images to 1
        config(['hero.max_images' => 1]);

        $file1 = UploadedFile::fake()->image('slide1.jpg');
        $file2 = UploadedFile::fake()->image('slide2.jpg');

        $response = $this->actingAs($admin)
            ->post(route('admin.hero.settings.update'), [
                'images' => [$file1, $file2],
                'sort_order' => [],
            ]);

        $response->assertSessionHasErrors('images');
    }

    /**
     * Test admin can replace existing slide image.
     */
    public function test_admin_can_replace_existing_slide_image(): void
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
        $oldPath = Storage::disk('public')->putFile('hero', UploadedFile::fake()->image('old.jpg'));
        $slide = HeroImage::create([
            'image_path' => $oldPath,
            'sort_order' => 1,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        $replacementFile = UploadedFile::fake()->image('replacement.jpg');

        $response = $this->actingAs($admin)
            ->post(route('admin.hero.settings.update'), [
                'replace_images' => [
                    $slide->id => $replacementFile
                ],
                'sort_order' => [
                    $slide->id => 1
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert record is updated
        $updatedSlide = HeroImage::findOrFail($slide->id);
        $this->assertNotEquals($oldPath, $updatedSlide->image_path);

        // Assert old file deleted and new file stored
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($updatedSlide->image_path);
    }

    /**
     * Test admin can upload three responsive images for a single slide.
     */
    public function test_admin_can_upload_responsive_images(): void
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

        $desktop = UploadedFile::fake()->image('desktop.jpg');
        $tablet = UploadedFile::fake()->image('tablet.jpg');
        $mobile = UploadedFile::fake()->image('mobile.jpg');

        $response = $this->actingAs($admin)
            ->post(route('admin.hero.settings.update'), [
                'new_desktop' => [0 => $desktop],
                'new_tablet' => [0 => $tablet],
                'new_mobile' => [0 => $mobile],
                'new_urls' => [0 => 'https://google.com'],
                'sort_order' => [],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(1, HeroImage::count());
        $slide = HeroImage::first();
        $this->assertEquals('https://google.com', $slide->url);
        
        Storage::disk('public')->assertExists($slide->image_desktop);
        Storage::disk('public')->assertExists($slide->image_tablet);
        Storage::disk('public')->assertExists($slide->image_mobile);
        $this->assertEquals($slide->image_desktop, $slide->image_path);
    }

    /**
     * Test admin can save a slide with only one image out of the three options.
     */
    public function test_admin_can_upload_single_responsive_image(): void
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

        $mobile = UploadedFile::fake()->image('mobile.jpg');

        $response = $this->actingAs($admin)
            ->post(route('admin.hero.settings.update'), [
                'new_mobile' => [0 => $mobile],
                'new_urls' => [0 => 'https://google.com'],
                'sort_order' => [],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(1, HeroImage::count());
        $slide = HeroImage::first();
        $this->assertNull($slide->image_desktop);
        $this->assertNull($slide->image_tablet);
        Storage::disk('public')->assertExists($slide->image_mobile);
        $this->assertEquals($slide->image_mobile, $slide->image_path);
    }

    /**
     * Test admin can replace responsive images on an existing slide.
     */
    public function test_admin_can_replace_responsive_images(): void
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

        // Pre-create slide with Desktop, Tablet, Mobile images
        $desktopPath = Storage::disk('public')->putFile('hero', UploadedFile::fake()->image('old_desktop.jpg'));
        $tabletPath = Storage::disk('public')->putFile('hero', UploadedFile::fake()->image('old_tablet.jpg'));
        $mobilePath = Storage::disk('public')->putFile('hero', UploadedFile::fake()->image('old_mobile.jpg'));

        $slide = HeroImage::create([
            'image_desktop' => $desktopPath,
            'image_tablet' => $tabletPath,
            'image_mobile' => $mobilePath,
            'image_path' => $desktopPath,
            'sort_order' => 1,
        ]);

        Storage::disk('public')->assertExists($desktopPath);
        Storage::disk('public')->assertExists($tabletPath);
        Storage::disk('public')->assertExists($mobilePath);

        $newTablet = UploadedFile::fake()->image('new_tablet.jpg');

        $response = $this->actingAs($admin)
            ->post(route('admin.hero.settings.update'), [
                'replace_tablet' => [
                    $slide->id => $newTablet
                ],
                'sort_order' => [
                    $slide->id => 1
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $updated = HeroImage::findOrFail($slide->id);
        Storage::disk('public')->assertExists($updated->image_desktop);
        Storage::disk('public')->assertMissing($tabletPath);
        Storage::disk('public')->assertExists($updated->image_tablet);
        Storage::disk('public')->assertExists($updated->image_mobile);
    }

    /**
     * Test deleting a slide deletes all device images associated with it.
     */
    public function test_deleting_slide_deletes_all_responsive_images(): void
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

        $desktopPath = Storage::disk('public')->putFile('hero', UploadedFile::fake()->image('desktop.jpg'));
        $tabletPath = Storage::disk('public')->putFile('hero', UploadedFile::fake()->image('tablet.jpg'));
        $mobilePath = Storage::disk('public')->putFile('hero', UploadedFile::fake()->image('mobile.jpg'));

        $slide = HeroImage::create([
            'image_desktop' => $desktopPath,
            'image_tablet' => $tabletPath,
            'image_mobile' => $mobilePath,
            'image_path' => $desktopPath,
            'sort_order' => 1,
        ]);

        Storage::disk('public')->assertExists($desktopPath);
        Storage::disk('public')->assertExists($tabletPath);
        Storage::disk('public')->assertExists($mobilePath);

        $response = $this->actingAs($admin)
            ->delete(route('admin.hero.settings.delete-image', $slide->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(0, HeroImage::count());
        Storage::disk('public')->assertMissing($desktopPath);
        Storage::disk('public')->assertMissing($tabletPath);
        Storage::disk('public')->assertMissing($mobilePath);
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
