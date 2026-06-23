<?php

namespace SGCart\ProductVariants\Http\Controllers;

use App\Http\Controllers\Controller;
use SGCart\ProductVariants\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function index()
    {
        $sizes = Size::latest()->paginate(10);
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
