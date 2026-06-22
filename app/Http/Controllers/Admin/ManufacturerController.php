<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ManufacturerController extends Controller
{
    public function index(Request $request)
    {
        $query = Manufacturer::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $manufacturers = $query->paginate(10)->withQueryString();
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

        return redirect()->route('admin.manufacturers.index')->with('success', 'Manufacturer is created successfully.');
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

        return redirect()->route('admin.manufacturers.edit', $manufacturer)->with('success', 'Manufacturer is updated successfully.');
    }

    public function destroy(Manufacturer $manufacturer)
    {
        if ($manufacturer->logo) Storage::disk('public')->delete($manufacturer->logo);
        $manufacturer->delete();
        return redirect()->route('admin.manufacturers.index')->with('success', 'Manufacturer is deleted successfully.');
    }
}