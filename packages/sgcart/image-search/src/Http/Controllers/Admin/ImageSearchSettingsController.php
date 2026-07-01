<?php

namespace SGCart\ImageSearch\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SGCart\ImageSearch\Models\ImageSearchSetting;

class ImageSearchSettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $setting = ImageSearchSetting::first();

        if (!$setting) {
            $provider = config('image-search.image_analyzer_provider') ?: config('ai.default_for_images') ?: 'gemini';
            $model = config('image-search.image_analyzer_model') ?: 'gemini-2.5-flash';
            
            // Resolve initial API key from env
            $apiKeyEnvName = strtoupper($provider) . '_API_KEY';
            $apiKey = env($apiKeyEnvName) ?: env('GEMINI_API_KEY') ?: env('OPENAI_API_KEY');

            $setting = new ImageSearchSetting([
                'provider' => $provider,
                'model' => $model,
                'api_key' => $apiKey,
                'is_active' => true,
            ]);
        }

        return view('image-search::admin.settings', compact('setting'));
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $setting = ImageSearchSetting::first();

        if ($request->input('action') === 'toggle') {
            if (!$setting) {
                $provider = config('image-search.image_analyzer_provider') ?: config('ai.default_for_images') ?: 'gemini';
                $model = config('image-search.image_analyzer_model') ?: 'gemini-2.5-flash';
                $apiKeyEnvName = strtoupper($provider) . '_API_KEY';
                $apiKey = env($apiKeyEnvName) ?: env('GEMINI_API_KEY') ?: env('OPENAI_API_KEY');

                $setting = ImageSearchSetting::create([
                    'provider' => $provider,
                    'model' => $model,
                    'api_key' => $apiKey,
                    'is_active' => false, // toggled from active (default) to inactive
                ]);
            } else {
                $setting->update([
                    'is_active' => !$setting->is_active,
                ]);
            }

            $statusText = $setting->is_active ? 'activated' : 'deactivated';
            return redirect()->back()->with('success', "Image search integration successfully {$statusText}.");
        }

        $data = $request->validate([
            'provider' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'api_key' => 'required|string',
        ]);

        if (!$setting) {
            $setting = ImageSearchSetting::create([
                ...$data,
                'is_active' => true,
            ]);
        } else {
            $setting->update($data);
        }

        return redirect()->back()->with('success', 'Image search settings saved successfully.');
    }
}
