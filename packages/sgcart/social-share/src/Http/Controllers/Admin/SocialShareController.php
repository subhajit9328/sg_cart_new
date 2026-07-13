<?php

namespace SGCart\SocialShare\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SGCart\SocialShare\Models\SocialPost;
use Illuminate\Support\Facades\Storage;

class SocialShareController extends Controller
{
    public function index(Request $request)
    {
        $query = SocialPost::with(['customer', 'product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $posts = $query->latest()->paginate(15);

        return view('social-share::admin.index', compact('posts'));
    }

    public function approve($id)
    {
        $post = SocialPost::findOrFail($id);
        $post->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => null
        ]);

        return redirect()->route('admin.social-shares.index')
            ->with('success', 'Social post approved successfully and is now live on the storefront!');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $post = SocialPost::findOrFail($id);
        $post->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $request->input('rejection_reason'),
        ]);

        return redirect()->route('admin.social-shares.index')
            ->with('success', 'Social post request rejected.');
    }

    public function destroy($id)
    {
        $post = SocialPost::findOrFail($id);
        
        // Delete file from disk
        if (Storage::disk('public')->exists($post->media_path)) {
            Storage::disk('public')->delete($post->media_path);
        }

        $post->delete();

        return redirect()->route('admin.social-shares.index')
            ->with('success', 'Social post deleted successfully.');
    }
}
