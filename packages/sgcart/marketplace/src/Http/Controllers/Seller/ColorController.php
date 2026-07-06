<?php

namespace SGCart\Marketplace\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use SGCart\ProductVariants\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $sellerId = Auth::guard('seller')->id();
        $query = Color::whereNull('seller_id')->orWhere('seller_id', $sellerId);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('hex_code', 'like', '%' . $search . '%');
            });
        }

        $sortBy = $request->input('sort_by');
        $sortOrder = $request->input('sort_order') ?? $request->input('sort_dir') ?? 'desc';
        $allowedSortFields = ['name', 'hex_code', 'created_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $colors = $query->paginate(10)->withQueryString();
        return view('marketplace::seller.colors.index', compact('colors'));
    }

    public function create()
    {
        return view('marketplace::seller.colors.create');
    }

    public function store(Request $request)
    {
        $sellerId = Auth::guard('seller')->id();
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($sellerId) {
                    $exists = Color::where('name', $value)
                        ->where(function ($query) use ($sellerId) {
                            $query->whereNull('seller_id')
                                  ->orWhere('seller_id', $sellerId);
                        })
                        ->exists();
                    if ($exists) {
                        $fail('The color name has already been taken.');
                    }
                }
            ],
            'hex_code' => 'required|string|max:10',
        ]);

        Color::create([
            'name' => $request->name,
            'hex_code' => $request->hex_code,
            'seller_id' => $sellerId,
        ]);

        return redirect()->route('seller.colors.index')->with('success', 'Color swatch created successfully.');
    }

    public function edit(Color $color)
    {
        $sellerId = Auth::guard('seller')->id();
        if ($color->seller_id !== $sellerId) {
            abort(403, 'Unauthorized action.');
        }

        return view('marketplace::seller.colors.edit', compact('color'));
    }

    public function update(Request $request, Color $color)
    {
        $sellerId = Auth::guard('seller')->id();
        if ($color->seller_id !== $sellerId) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($sellerId, $color) {
                    $exists = Color::where('name', $value)
                        ->where('id', '!=', $color->id)
                        ->where(function ($query) use ($sellerId) {
                            $query->whereNull('seller_id')
                                  ->orWhere('seller_id', $sellerId);
                        })
                        ->exists();
                    if ($exists) {
                        $fail('The color name has already been taken.');
                    }
                }
            ],
            'hex_code' => 'required|string|max:10',
        ]);

        $color->update([
            'name' => $request->name,
            'hex_code' => $request->hex_code,
        ]);

        return redirect()->route('seller.colors.index')->with('success', 'Color swatch updated successfully.');
    }

    public function destroy(Color $color)
    {
        $sellerId = Auth::guard('seller')->id();
        if ($color->seller_id !== $sellerId) {
            abort(403, 'Unauthorized action.');
        }

        $color->delete();
        return redirect()->route('seller.colors.index')->with('success', 'Color swatch deleted successfully.');
    }
}
