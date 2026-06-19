<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductSpecification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'manufacturer', 'primaryImage'])
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
            // FK exists rules always reference the integer id column — never exposed in URLs
            'category_id'       => 'nullable|exists:categories,id',
            'manufacturer_id'   => 'nullable|exists:manufacturers,id',
            'short_description' => 'nullable|string',
            'description'       => 'nullable|string',
            'price'             => 'required|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0',
            'stock'             => 'required|integer|min:0',
            'status'            => 'required|in:draft,active,inactive',
            'is_featured'       => 'boolean',
            'weight'            => 'nullable|string',
            'dimensions'        => 'nullable|string',
            // Variants
            'variants'              => 'nullable|array',
            'variants.*.sku'        => 'required_with:variants|string',
            'variants.*.size'       => 'nullable|string',
            'variants.*.colour'     => 'nullable|string',
            'variants.*.weight'     => 'nullable|string',
            'variants.*.price'      => 'nullable|numeric',
            'variants.*.stock'      => 'nullable|integer',
            // Specs
            'specs'             => 'nullable|array',
            'specs.*.label'     => 'required_with:specs|string',
            'specs.*.value'     => 'required_with:specs|string',
            // Images
            'images'              => 'nullable|array',
            'images.*'            => 'image|max:4096',
            'primary_image_index' => 'nullable|integer',
        ]);

        DB::transaction(function () use ($data, $request) {
            $data['slug'] = Str::slug($data['name']);

            $product = Product::create(collect($data)->except(['variants', 'specs', 'images', 'primary_image_index'])->toArray());

            // Variants
            foreach ($data['variants'] ?? [] as $v) {
                $product->variants()->create($v);
            }

            // Specs
            foreach ($data['specs'] ?? [] as $i => $spec) {
                $product->specifications()->create([...$spec, 'sort_order' => $i]);
            }

            // Images
            if ($request->hasFile('images')) {
                $primary = $request->input('primary_image_index', 0);
                foreach ($request->file('images') as $i => $file) {
                    $product->images()->create([
                        'path'       => $file->store('products', 'public'),
                        'alt'        => $product->name,
                        'is_primary' => $i === (int) $primary,
                        'sort_order' => $i,
                    ]);
                }
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    /**
     * Route model binding resolves Product by `ulid` column automatically.
     */
    public function edit(Product $product)
    {
        $product->load('variants', 'images', 'specifications');
        $categories    = Category::where('is_active', true)->get();
        $manufacturers = Manufacturer::where('is_active', true)->get();
        return view('admin.products.edit', compact('product', 'categories', 'manufacturers'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            // Unique ignore-self uses the internal integer id — safe because it never appears in URLs
            'sku'               => 'required|string|unique:products,sku,' . $product->id,
            'category_id'       => 'nullable|exists:categories,id',
            'manufacturer_id'   => 'nullable|exists:manufacturers,id',
            'short_description' => 'nullable|string',
            'description'       => 'nullable|string',
            'price'             => 'required|numeric|min:0',
            'sale_price'        => 'nullable|numeric|min:0',
            'stock'             => 'required|integer|min:0',
            'status'            => 'required|in:draft,active,inactive',
            'is_featured'       => 'boolean',
            'weight'            => 'nullable|string',
            'dimensions'        => 'nullable|string',
            'variants'              => 'nullable|array',
            'variants.*.id'         => 'nullable|exists:product_variants,id',
            'variants.*.sku'        => 'required_with:variants|string',
            'variants.*.size'       => 'nullable|string',
            'variants.*.colour'     => 'nullable|string',
            'variants.*.weight'     => 'nullable|string',
            'variants.*.price'      => 'nullable|numeric',
            'variants.*.stock'      => 'nullable|integer',
            'specs'             => 'nullable|array',
            'specs.*.label'     => 'required_with:specs|string',
            'specs.*.value'     => 'required_with:specs|string',
            'images'              => 'nullable|array',
            'images.*'            => 'image|max:4096',
            'primary_image_id'    => 'nullable|exists:product_images,id',
        ]);

        DB::transaction(function () use ($data, $request, $product) {
            $data['slug'] = Str::slug($data['name']);
            $product->update(collect($data)->except(['variants', 'specs', 'images', 'primary_image_id'])->toArray());

            // Sync variants: update existing, create new
            $incomingIds = collect($data['variants'] ?? [])->pluck('id')->filter()->toArray();
            $product->variants()->whereNotIn('id', $incomingIds)->delete();

            foreach ($data['variants'] ?? [] as $v) {
                if (!empty($v['id'])) {
                    ProductVariant::find($v['id'])?->update($v);
                } else {
                    $product->variants()->create($v);
                }
            }

            // Sync specs
            $product->specifications()->delete();
            foreach ($data['specs'] ?? [] as $i => $spec) {
                $product->specifications()->create([...$spec, 'sort_order' => $i]);
            }

            // New images
            if ($request->hasFile('images')) {
                $nextOrder = $product->images()->max('sort_order') + 1;
                foreach ($request->file('images') as $i => $file) {
                    $product->images()->create([
                        'path'       => $file->store('products', 'public'),
                        'alt'        => $product->name,
                        'is_primary' => false,
                        'sort_order' => $nextOrder + $i,
                    ]);
                }
            }

            // Set primary image
            if ($primaryId = $request->input('primary_image_id')) {
                $product->images()->update(['is_primary' => false]);
                $product->images()->where('id', $primaryId)->update(['is_primary' => true]);
            }
        });

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $img) {
            Storage::disk('public')->delete($img->path);
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }

    public function destroyImage(ProductImage $image)
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();
        return back()->with('success', 'Image removed.');
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
                        'slug'              => Str::slug($name),
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
}