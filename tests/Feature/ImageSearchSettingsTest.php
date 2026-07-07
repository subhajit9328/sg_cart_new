<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SGCart\ImageSearch\Models\ImageSearchSetting;
use Tests\TestCase;

class ImageSearchSettingsTest extends TestCase
{
    use RefreshDatabase;

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

        $response = $this->actingAs($admin)
            ->get(route('admin.image-search.settings'));

        $response->assertStatus(200);
        $response->assertSee('Search Settings');
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
}
