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
            'sort_order' => 'nullable|array',
            'sort_order.*' => 'required|integer',
        ]);

        // 1. Handle image slide uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('hero', 'public');
                HeroImage::create([
                    'image_path' => $path,
                    'sort_order' => 0,
                ]);
            }
        }

        // 2. Update existing slide sorting orders
        if ($request->has('sort_order')) {
            foreach ($request->input('sort_order') as $id => $order) {
                HeroImage::where('id', $id)->update([
                    'sort_order' => (int) $order,
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

        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
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
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
            $image->delete();
        }

        return redirect()->back()->with('success', 'Selected hero slide images deleted successfully.');
    }
}
