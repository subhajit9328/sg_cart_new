<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ManufacturerController extends Controller
{
    public function index()
    {
        $manufacturers = Manufacturer::paginate(20);
        return view('admin.manufacturers.index', compact('manufacturers'));
    }

    public function create()
    {
        return view('admin.manufacturers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'website'     => 'nullable|url',
            'email'       => 'nullable|email',
            'phone'       => 'nullable|string|max:30',
            'address'     => 'nullable|string',
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|max:2048',
            'is_active'   => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('manufacturers', 'public');
        }

        Manufacturer::create($data);

        return redirect()->route('admin.manufacturers.index')->with('success', 'Manufacturer created.');
    }

    public function edit(Manufacturer $manufacturer)
    {
        return view('admin.manufacturers.edit', compact('manufacturer'));
    }

    public function update(Request $request, Manufacturer $manufacturer)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'website'     => 'nullable|url',
            'email'       => 'nullable|email',
            'phone'       => 'nullable|string|max:30',
            'address'     => 'nullable|string',
            'description' => 'nullable|string',
            'logo'        => 'nullable|image|max:2048',
            'is_active'   => 'boolean',
        ]);

        $data['slug'] = Str::slug($data['name']);

        if ($request->hasFile('logo')) {
            if ($manufacturer->logo) Storage::disk('public')->delete($manufacturer->logo);
            $data['logo'] = $request->file('logo')->store('manufacturers', 'public');
        }

        $manufacturer->update($data);

        return redirect()->route('admin.manufacturers.index')->with('success', 'Manufacturer updated.');
    }

    public function destroy(Manufacturer $manufacturer)
    {
        if ($manufacturer->logo) Storage::disk('public')->delete($manufacturer->logo);
        $manufacturer->delete();
        return redirect()->route('admin.manufacturers.index')->with('success', 'Manufacturer deleted.');
    }
}