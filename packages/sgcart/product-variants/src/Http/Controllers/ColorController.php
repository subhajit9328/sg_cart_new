<?php

namespace SGCart\ProductVariants\Http\Controllers;

use App\Http\Controllers\Controller;
use SGCart\ProductVariants\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $query = Color::with('seller');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('hex_code', 'like', '%' . $search . '%');
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
        return view('product-variants::colors.index', compact('colors'));
    }

    public function create()
    {
        return view('product-variants::colors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|unique:colors,name|max:255',
            'hex_code' => 'required|string|max:10',
        ]);

        Color::create($data);

        return redirect()->route('admin.colors.index')->with('success', 'Color swatch created successfully.');
    }

    public function edit(Color $color)
    {
        return view('product-variants::colors.edit', compact('color'));
    }

    public function update(Request $request, Color $color)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:colors,name,' . $color->id,
            'hex_code' => 'required|string|max:10',
        ]);

        $color->update($data);

        return redirect()->route('admin.colors.index')->with('success', 'Color swatch updated successfully.');
    }

    public function destroy(Color $color)
    {
        if ($color->variants()->whereHas('product')->exists()) {
            return redirect()->route('admin.colors.index')->with('error',
                'Color swatch cannot be deleted because it is associated with a product variant.');
        }
        $color->delete();
        return redirect()->route('admin.colors.index')->with('success', 'Color swatch deleted successfully.');
    }
}
