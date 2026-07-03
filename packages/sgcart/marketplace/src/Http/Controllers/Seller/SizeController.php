<?php

namespace SGCart\Marketplace\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use SGCart\ProductVariants\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SizeController extends Controller
{
    public function index(Request $request)
    {
        $sellerId = Auth::guard('seller')->id();
        $query = Size::whereNull('seller_id')->orWhere('seller_id', $sellerId);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
            });
        }

        $sortBy = $request->input('sort_by');
        $sortOrder = $request->input('sort_order') ?? $request->input('sort_dir') ?? 'desc';
        $allowedSortFields = ['name', 'code', 'created_at'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $sizes = $query->paginate(10)->withQueryString();
        return view('marketplace::seller.sizes.index', compact('sizes'));
    }

    public function create()
    {
        return view('marketplace::seller.sizes.create');
    }

    public function store(Request $request)
    {
        $sellerId = Auth::guard('seller')->id();
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:10',
                function ($attribute, $value, $fail) use ($sellerId) {
                    $exists = Size::where('code', $value)
                        ->where(function ($query) use ($sellerId) {
                            $query->whereNull('seller_id')
                                  ->orWhere('seller_id', $sellerId);
                        })
                        ->exists();
                    if ($exists) {
                        $fail('The size code has already been taken.');
                    }
                }
            ],
        ]);

        Size::create([
            'name' => $request->name,
            'code' => $request->code,
            'seller_id' => $sellerId,
        ]);

        return redirect()->route('seller.sizes.index')->with('success', 'Size swatch created successfully.');
    }

    public function edit(Size $size)
    {
        $sellerId = Auth::guard('seller')->id();
        if ($size->seller_id !== $sellerId) {
            abort(403, 'Unauthorized action.');
        }

        return view('marketplace::seller.sizes.edit', compact('size'));
    }

    public function update(Request $request, Size $size)
    {
        $sellerId = Auth::guard('seller')->id();
        if ($size->seller_id !== $sellerId) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:10',
                function ($attribute, $value, $fail) use ($sellerId, $size) {
                    $exists = Size::where('code', $value)
                        ->where('id', '!=', $size->id)
                        ->where(function ($query) use ($sellerId) {
                            $query->whereNull('seller_id')
                                  ->orWhere('seller_id', $sellerId);
                        })
                        ->exists();
                    if ($exists) {
                        $fail('The size code has already been taken.');
                    }
                }
            ],
        ]);

        $size->update([
            'name' => $request->name,
            'code' => $request->code,
        ]);

        return redirect()->route('seller.sizes.index')->with('success', 'Size swatch updated successfully.');
    }

    public function destroy(Size $size)
    {
        $sellerId = Auth::guard('seller')->id();
        if ($size->seller_id !== $sellerId) {
            abort(403, 'Unauthorized action.');
        }

        $size->delete();
        return redirect()->route('seller.sizes.index')->with('success', 'Size swatch deleted successfully.');
    }
}
