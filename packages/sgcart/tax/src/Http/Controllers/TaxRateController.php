<?php

namespace SGCart\Tax\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SGCart\Tax\Models\TaxRate;
use SGCart\Tax\Models\TaxSetting;

class TaxRateController extends Controller
{
    /**
     * Display a listing of tax rates (Admin).
     */
    public function index()
    {
        $rates = TaxRate::orderBy('id', 'desc')->paginate(10);
        $calculationMode = TaxSetting::getVal('tax_calculation_mode', 'single_standard');
        return view('tax::index', compact('rates', 'calculationMode'));
    }

    /**
     * Show the form for creating a new tax rate (Admin).
     */
    public function create()
    {
        return view('tax::create');
    }

    /**
     * Store a newly created tax rate in storage (Admin).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:flat,percent',
            'rate' => 'required|numeric|min:0',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        TaxRate::create([
            'name' => trim($request->name),
            'type' => $request->type,
            'rate' => $request->rate,
            'country' => $request->country,
            'state' => $request->state,
            'zip' => $request->zip,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.tax.index')->with('success', 'Tax rate created successfully!');
    }

    /**
     * Show the form for editing the specified tax rate (Admin).
     */
    public function edit($id)
    {
        $rate = TaxRate::findOrFail($id);
        return view('tax::edit', compact('rate'));
    }

    /**
     * Update the specified tax rate in storage (Admin).
     */
    public function update(Request $request, $id)
    {
        $rate = TaxRate::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:flat,percent',
            'rate' => 'required|numeric|min:0',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $rate->update([
            'name' => trim($request->name),
            'type' => $request->type,
            'rate' => $request->rate,
            'country' => $request->country,
            'state' => $request->state,
            'zip' => $request->zip,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.tax.index')->with('success', 'Tax rate updated successfully!');
    }

    /**
     * Remove the specified tax rate from storage (Admin).
     */
    public function destroy($id)
    {
        $rate = TaxRate::findOrFail($id);
        $rate->delete();

        return redirect()->route('admin.tax.index')->with('success', 'Tax rate deleted successfully!');
    }

    /**
     * Update tax global settings (Admin).
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'tax_calculation_mode' => 'required|string|in:single_standard,regional_dynamic',
        ]);

        TaxSetting::setVal('tax_calculation_mode', $request->tax_calculation_mode);

        return redirect()->route('admin.tax.index')->with('success', 'Tax configuration settings updated successfully!');
    }
}
