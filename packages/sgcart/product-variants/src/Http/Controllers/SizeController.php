<?php

namespace SGCart\ProductVariants\Http\Controllers;

use App\Http\Controllers\Controller;
use SGCart\ProductVariants\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function index(Request $request)
    {
        $query = Size::query();

        if ($search = $request->input('search')) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('code', 'like', '%' . $search . '%');
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
        return view('product-variants::sizes.index', compact('sizes'));
    }

    public function create()
    {
        return view('product-variants::sizes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:sizes,code|max:10',
        ]);

        Size::create($data);

        return redirect()->route('admin.sizes.index')->with('success', 'Size label created successfully.');
    }

    public function edit(Size $size)
    {
        return view('product-variants::sizes.edit', compact('size'));
    }

    public function update(Request $request, Size $size)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:sizes,code,' . $size->id,
        ]);

        $size->update($data);

        return redirect()->route('admin.sizes.index')->with('success', 'Size label updated successfully.');
    }

    public function destroy(Size $size)
    {
        $size->delete();
        return redirect()->route('admin.sizes.index')->with('success', 'Size label deleted successfully.');
    }
}
