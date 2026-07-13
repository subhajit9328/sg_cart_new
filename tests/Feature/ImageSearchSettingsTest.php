<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SGCart\ImageSearch\Models\ImageSearchSetting;
use Tests\TestCase;

class ImageSearchSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear Spatie's permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create the permission
        \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => 'manage image search config',
            'guard_name' => 'web'
        ]);
    }

    /**
     * Test admin can view image search settings.
     */
    public function test_admin_can_view_image_search_settings(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);
        $admin->givePermissionTo('manage image search config');

        $response = $this->actingAs($admin)
            ->get(route('admin.image-search.settings'));

        $response->assertStatus(200);
        $response->assertSee('Search Settings');
    }

    /**
     * Test unauthorized user cannot view image search settings.
     */
    public function test_unauthorized_user_cannot_view_image_search_settings(): void
    {
        $user = User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.image-search.settings'));

        $response->assertStatus(403);
    }

    /**
     * Test admin api key validation message.
     */
    public function test_api_key_validation_custom_message(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);
        $admin->givePermissionTo('manage image search config');

        $response = $this->actingAs($admin)
            ->post(route('admin.image-search.settings.update'), [
                'provider' => 'gemini',
                'model' => 'gemini-2.5-flash',
                'api_key' => '', // empty api_key triggers required
            ]);

        $response->assertSessionHasErrors([
            'api_key' => 'API Key required.'
        ]);
    }

    /**
     * Test save settings button is disabled on validation errors.
     */
    public function test_save_button_disabled_on_validation_error(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);
        $admin->givePermissionTo('manage image search config');

        // Request update with invalid parameters to trigger validation redirect
        $response = $this->actingAs($admin)
            ->from(route('admin.image-search.settings'))
            ->post(route('admin.image-search.settings.update'), [
                'provider' => '',
                'model' => '',
                'api_key' => '',
            ]);

        $response->assertRedirect(route('admin.image-search.settings'));

        // Visit settings page with validation errors in session
        $followResponse = $this->actingAs($admin)
            ->get(route('admin.image-search.settings'));

        $followResponse->assertStatus(200);
        $followResponse->assertSee('disabled');
    }
}
