<?php

namespace SGCart\ProductVariants\Http\Controllers;

use App\Http\Controllers\Controller;
use SGCart\ProductVariants\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index()
    {
        $colors = Color::latest()->paginate(10);
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
        $color->delete();
        return redirect()->route('admin.colors.index')->with('success', 'Color swatch deleted successfully.');
    }
}
