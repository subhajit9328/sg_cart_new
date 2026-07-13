<?php

namespace SGCart\Hero\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use SGCart\Hero\Models\HeroImage;

class HeroController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $images = HeroImage::orderBy('sort_order')->orderBy('id')->get();

        return view('hero::admin.settings', compact('images'));
    }

    /**
     * Update settings, upload new slides, and update ordering.
     */
    public function update(Request $request)
    {
        $request->validate([
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'new_desktop' => 'nullable|array',
            'new_desktop.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'new_tablet' => 'nullable|array',
            'new_tablet.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'new_mobile' => 'nullable|array',
            'new_mobile.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'new_urls' => 'nullable|array',
            
            'replace_images' => 'nullable|array',
            'replace_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'replace_desktop' => 'nullable|array',
            'replace_desktop.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'replace_tablet' => 'nullable|array',
            'replace_tablet.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',
            'replace_mobile' => 'nullable|array',
            'replace_mobile.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:4096',

            'urls' => 'nullable|array',
            'sort_order' => 'nullable|array',
            'sort_order.*' => 'required|integer',
        ]);

        $maxImages = config('hero.max_images', 5);
        $currentCount = HeroImage::count();

        $newIndices = array_unique(array_merge(
            $request->hasFile('new_desktop') ? array_keys($request->file('new_desktop')) : [],
            $request->hasFile('new_tablet') ? array_keys($request->file('new_tablet')) : [],
            $request->hasFile('new_mobile') ? array_keys($request->file('new_mobile')) : []
        ));

        $newCount = count($newIndices);
        if ($request->hasFile('images')) {
            $newCount += count($request->file('images'));
        }

        if ($currentCount + $newCount > $maxImages) {
            $message = "You cannot have more than {$maxImages} slide images in total. Current count is {$currentCount}.";
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }
            return redirect()->back()->withErrors(['images' => $message]);
        }

        // Validate that each new slide index has at least one of desktop, tablet, or mobile images
        foreach ($newIndices as $index) {
            $hasDesktop = $request->hasFile("new_desktop.{$index}");
            $hasTablet = $request->hasFile("new_tablet.{$index}");
            $hasMobile = $request->hasFile("new_mobile.{$index}");

            if (!$hasDesktop && !$hasTablet && !$hasMobile) {
                $message = "At least one image (Desktop, Tablet, or Mobile) is required for each new slide.";
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                    ], 422);
                }
                return redirect()->back()->withErrors(['new_desktop' => $message]);
            }
        }

        // 1. Handle old image slide uploads (backward compatibility)
        if ($request->hasFile('images')) {
            $newUrls = $request->input('new_urls', []);
            $maxSortOrder = HeroImage::max('sort_order') ?? 0;
            
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('hero', 'public');
                $url = isset($newUrls[$index]) ? $newUrls[$index] : null;
                $maxSortOrder++;
                HeroImage::create([
                    'image_path' => $path,
                    'image_desktop' => $path,
                    'image_tablet' => $path,
                    'image_mobile' => $path,
                    'url' => $url,
                    'sort_order' => $maxSortOrder,
                ]);
            }
        }

        // 2. Handle new device-specific image slide uploads
        if (!empty($newIndices)) {
            $newUrls = $request->input('new_urls', []);
            $maxSortOrder = HeroImage::max('sort_order') ?? 0;
            
            foreach ($newIndices as $index) {
                $desktopPath = $request->hasFile("new_desktop.{$index}") 
                    ? $request->file("new_desktop.{$index}")->store('hero', 'public') 
                    : null;
                $tabletPath = $request->hasFile("new_tablet.{$index}") 
                    ? $request->file("new_tablet.{$index}")->store('hero', 'public') 
                    : null;
                $mobilePath = $request->hasFile("new_mobile.{$index}") 
                    ? $request->file("new_mobile.{$index}")->store('hero', 'public') 
                    : null;

                $url = isset($newUrls[$index]) ? $newUrls[$index] : null;
                $maxSortOrder++;

                HeroImage::create([
                    'image_desktop' => $desktopPath,
                    'image_tablet' => $tabletPath,
                    'image_mobile' => $mobilePath,
                    'image_path' => $desktopPath ?: $tabletPath ?: $mobilePath,
                    'url' => $url,
                    'sort_order' => $maxSortOrder,
                ]);
            }
        }

        // 3. Handle old style specific slide image replacements
        if ($request->hasFile('replace_images')) {
            foreach ($request->file('replace_images') as $id => $file) {
                $image = HeroImage::findOrFail($id);
                if ($image->image_path && Storage::disk('public')->exists($image->image_path)) {
                    Storage::disk('public')->delete($image->image_path);
                }
                $path = $file->store('hero', 'public');
                $image->update([
                    'image_path' => $path,
                    'image_desktop' => $path,
                ]);
            }
        }

        // 4. Handle new device-specific slide image replacements
        $replaceFields = [
            'replace_desktop' => 'image_desktop',
            'replace_tablet' => 'image_tablet',
            'replace_mobile' => 'image_mobile',
        ];

        foreach ($replaceFields as $inputKey => $dbField) {
            if ($request->hasFile($inputKey)) {
                foreach ($request->file($inputKey) as $id => $file) {
                    $image = HeroImage::findOrFail($id);
                    if ($image->$dbField && Storage::disk('public')->exists($image->$dbField)) {
                        Storage::disk('public')->delete($image->$dbField);
                    }
                    $path = $file->store('hero', 'public');
                    $image->update([
                        $dbField => $path,
                    ]);

                    // Keep image_path synchronized with a valid image path fallback
                    $image->update([
                        'image_path' => $image->image_desktop ?: $image->image_tablet ?: $image->image_mobile
                    ]);
                }
            }
        }

        // 5. Update existing slide sorting orders and URLs
        if ($request->has('sort_order')) {
            $existingUrls = $request->input('urls', []);
            foreach ($request->input('sort_order') as $id => $order) {
                HeroImage::where('id', $id)->update([
                    'sort_order' => (int) $order,
                    'url' => isset($existingUrls[$id]) ? $existingUrls[$id] : null,
                ]);
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            session()->flash('success', 'Hero section configuration saved successfully.');
            return response()->json([
                'success' => true,
                'message' => 'Hero section configuration saved successfully.',
            ]);
        }

        return redirect()->back()->with('success', 'Hero section configuration saved successfully.');
    }

    /**
     * Delete an individual slide image.
     */
    public function deleteImage($id)
    {
        $image = HeroImage::findOrFail($id);

        foreach (['image_desktop', 'image_tablet', 'image_mobile', 'image_path'] as $field) {
            if ($image->$field && Storage::disk('public')->exists($image->$field)) {
                Storage::disk('public')->delete($image->$field);
            }
        }

        $image->delete();

        return redirect()->back()->with('success', 'Hero section image deleted successfully.');
    }

    /**
     * Delete multiple selected slide images.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:hero_images,id',
        ]);

        $images = HeroImage::whereIn('id', $request->input('ids'))->get();

        foreach ($images as $image) {
            foreach (['image_desktop', 'image_tablet', 'image_mobile', 'image_path'] as $field) {
                if ($image->$field && Storage::disk('public')->exists($image->$field)) {
                    Storage::disk('public')->delete($image->$field);
                }
            }
            $image->delete();
        }

        return redirect()->back()->with('success', 'Selected hero slide images deleted successfully.');
    }
}
