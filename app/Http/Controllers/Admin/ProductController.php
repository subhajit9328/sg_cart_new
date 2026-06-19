<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'manufacturer'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%")
                                                   ->orWhere('sku', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories    = Category::where('is_active', true)->get();
        $manufacturers = Manufacturer::where('is_active', true)->get();
        return view('admin.products.create', compact('categories', 'manufacturers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'sku'               => 'required|string|unique:products,sku',
            'category_id'       => 'nullable|exists:categories,id',
            'manufacturer_id'   => 'nullable|exists:manufacturers,id',
            'short_description' => 'nullable|string',
            'description'       => 'nullable|string',
            'price'             => 'required|numeric|gt:0',
            'sale_price'        => 'nullable|numeric|min:0',
            'stock'             => 'required|integer|min:0',
            'status'            => 'required|in:draft,active,inactive',
            'weight'            => 'nullable|string',
            'dimensions'        => 'nullable|string',
            'image'             => 'nullable|image|max:4096',
        ]);

        DB::transaction(function () use ($data, $request) {
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            Product::create($data);
        });

        return redirect()->route('admin.products.index')->with('success', 'Product is created successfully.');
    }

    public function edit(Product $product)
    {
        $categories    = Category::where('is_active', true)->get();
        $manufacturers = Manufacturer::where('is_active', true)->get();
        return view('admin.products.edit', compact('product', 'categories', 'manufacturers'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'sku'               => 'required|string|unique:products,sku,' . $product->id,
            'category_id'       => 'nullable|exists:categories,id',
            'manufacturer_id'   => 'nullable|exists:manufacturers,id',
            'short_description' => 'nullable|string',
            'description'       => 'nullable|string',
            'price'             => 'required|numeric|gt:0',
            'sale_price'        => 'nullable|numeric|min:0',
            'stock'             => 'required|integer|min:0',
            'status'            => 'required|in:draft,active,inactive',
            'weight'            => 'nullable|string',
            'dimensions'        => 'nullable|string',
            'image'             => 'nullable|image|max:4096',
        ]);

        DB::transaction(function () use ($data, $request, $product) {
            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);
        });

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product is updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product is deleted successfully.');
    }


    // ─── Bulk CSV/Excel Upload ────────────────────────────────────────────────
    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
        ]);

        $file      = $request->file('file');
        $extension = $file->getClientOriginalExtension();
        $rows      = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            // Requires: composer require phpoffice/phpspreadsheet
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            $headers     = array_shift($sheet);
            foreach ($sheet as $row) {
                $rows[] = array_combine($headers, $row);
            }
        } else {
            // CSV
            $handle  = fopen($file->getRealPath(), 'r');
            $headers = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = array_combine($headers, $row);
            }
            fclose($handle);
        }

        $created = 0;
        $errors  = [];

        foreach ($rows as $i => $row) {
            try {
                $name = trim($row['name'] ?? '');
                $sku  = trim($row['sku']  ?? '');

                if (!$name || !$sku) {
                    $errors[] = "Row " . ($i + 2) . ": name and sku are required.";
                    continue;
                }

                Product::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'name'              => $name,
                        'category_id'       => Category::where('name', $row['category'] ?? '')->value('id'),
                        'manufacturer_id'   => Manufacturer::where('name', $row['manufacturer'] ?? '')->value('id'),
                        'short_description' => $row['short_description'] ?? null,
                        'description'       => $row['description'] ?? null,
                        'price'             => $row['price'] ?? 0,
                        'sale_price'        => $row['sale_price'] ?? null,
                        'stock'             => $row['stock'] ?? 0,
                        'status'            => $row['status'] ?? 'draft',
                        'weight'            => $row['weight'] ?? null,
                        'dimensions'        => $row['dimensions'] ?? null,
                    ]
                );
                $created++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($i + 2) . ": " . $e->getMessage();
            }
        }

        $msg = "Imported {$created} products.";
        if ($errors) $msg .= ' Errors: ' . implode(' | ', $errors);

        return redirect()->route('admin.products.index')->with('success', $msg);
    }

    public function deleteImage(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
            $product->image = null;
            $product->save();
        }

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product image is deleted successfully.');
    }
}