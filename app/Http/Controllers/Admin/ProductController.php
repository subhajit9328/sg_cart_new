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
        $sortBy = $request->input('sort_by');
        $sortOrder = $request->input('sort_order') ?? $request->input('sort_dir') ?? 'desc';
        $allowedSortFields = ['name', 'sku', 'price', 'stock', 'status', 'created_at'];

        $query = Product::with(['category', 'manufacturer', 'seller'])
            ->whereNotIn('status', [\App\Enums\ProductStatus::PENDING_APPROVAL, \App\Enums\ProductStatus::REJECTED]);

        if (class_exists(\SGCart\Marketplace\Models\Seller::class)) {
            $query->where(function ($q) {
                $q->whereNull('seller_id')
                  ->orWhereHas('seller', function ($sub) {
                      $sub->where('status', 'approved');
                  });
            });
        }

        $products = $query
            ->when($request->search, fn ($q) => $q->where(fn($sq) => $sq->where('name', 'like', "%{$request->search}%")
                                                                         ->orWhere('sku', 'like', "%{$request->search}%")))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->seller_id, function ($q) use ($request) {
                if ($request->seller_id === 'admin') {
                    return $q->whereNull('seller_id');
                }
                return $q->where('seller_id', $request->seller_id);
            })
            ->when(in_array($sortBy, $allowedSortFields), fn ($q) => $q->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc'), fn ($q) => $q->latest())
            ->paginate(10)
            ->withQueryString();

        $categories = Category::all();
        
        $sellers = [];
        if (class_exists(\SGCart\Marketplace\Models\Seller::class)) {
            $sellers = \SGCart\Marketplace\Models\Seller::where('status', 'approved')->get();
        }

        return view('admin.products.index', compact('products', 'categories', 'sellers'));
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
            'sale_price'        => 'nullable|numeric|min:0|lte:price',
            'stock'             => 'required|integer|min:0',
            'min_stock'         => 'nullable|integer|min:0',
            'status'            => 'required|in:draft,active,inactive',
            'weight'            => 'nullable|string',
            'dimensions'        => 'nullable|string',
            'product_images'    => 'nullable|array',
            'product_images.*'  => 'image|max:4096',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string|max:1000',
            'meta_keywords'     => 'nullable|string|max:1000',
        ], [
            'sale_price.lte'    => 'Invalid Pricing: The sale price must be equal to or lower than the regular price.',
        ]);

        $product = DB::transaction(function () use ($data, $request) {
            $product = Product::create($data);

            $defaultImageValue = $request->input('default_image');

            if ($request->hasFile('product_images')) {
                $files = $request->file('product_images');
                
                $defaultKey = null;
                if ($defaultImageValue && str_starts_with($defaultImageValue, 'new_')) {
                    $defaultKey = substr($defaultImageValue, 4);
                }
                if (!$defaultKey || !isset($files[$defaultKey])) {
                    $defaultKey = array_key_first($files);
                }

                foreach ($files as $key => $file) {
                    $path = $file->store('products', 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'is_default' => ($key === $defaultKey)
                    ]);
                }
            }

            return $product;
        });

        if (\Route::has('admin.products.variants.grid')) {
            return redirect()->route('admin.products.edit', ['product' => $product, 'tab' => 'variants'])
                ->with('success', 'Product is created successfully.');
        }

        return redirect()->route('admin.products.index')->with('success', 'Product is created successfully.');
    }

    public function edit(Product $product)
    {
        $product->load('images');
        $categories    = Category::where('is_active', true)->get();
        $manufacturers = Manufacturer::where('is_active', true)->get();
        return view('admin.products.edit', compact('product', 'categories', 'manufacturers'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'sku'               => ['required', 'string', \Illuminate\Validation\Rule::unique('products', 'sku')->ignore($product->id)],
            'category_id'       => 'nullable|exists:categories,id',
            'manufacturer_id'   => 'nullable|exists:manufacturers,id',
            'short_description' => 'nullable|string',
            'description'       => 'nullable|string',
            'price'             => 'required|numeric|gt:0',
            'sale_price'        => 'nullable|numeric|min:0|lte:price',
            'stock'             => 'required|integer|min:0',
            'min_stock'         => 'nullable|integer|min:0',
            'status'            => 'required|in:draft,active,inactive',
            'weight'            => 'nullable|string',
            'dimensions'        => 'nullable|string',
            'product_images'    => 'nullable|array',
            'product_images.*'  => 'image|max:4096',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string|max:1000',
            'meta_keywords'     => 'nullable|string|max:1000',
        ], [
            'sale_price.lte'    => 'Invalid Pricing: The sale price must be equal to or lower than the regular price.',
        ]);

        DB::transaction(function () use ($data, $request, $product) {
            $product->update($data);

            // Handle deletions first
            if ($request->filled('deleted_images')) {
                $deletedIds = $request->input('deleted_images');
                $imagesToDelete = \App\Models\ProductImage::whereIn('id', $deletedIds)
                    ->where('product_id', $product->id)
                    ->get();
                foreach ($imagesToDelete as $imgToDelete) {
                    Storage::disk('public')->delete($imgToDelete->image_path);
                    $imgToDelete->delete();
                }
            }

            $defaultImageValue = $request->input('default_image');

            // Handle new uploads
            if ($request->hasFile('product_images')) {
                $files = $request->file('product_images');
                
                $defaultKey = null;
                if ($defaultImageValue && str_starts_with($defaultImageValue, 'new_')) {
                    $defaultKey = substr($defaultImageValue, 4);
                }

                foreach ($files as $key => $file) {
                    $path = $file->store('products', 'public');
                    $product->images()->create([
                        'image_path' => $path,
                        'is_default' => ($key === $defaultKey)
                    ]);
                }
            }

            // Sync/re-evaluate the default image flag
            $product->load('images');
            
            $targetDefaultImage = null;
            if ($defaultImageValue) {
                if (str_starts_with($defaultImageValue, 'existing_')) {
                    $id = (int) substr($defaultImageValue, 9);
                    $targetDefaultImage = $product->images->firstWhere('id', $id);
                } elseif (str_starts_with($defaultImageValue, 'new_')) {
                    $targetDefaultImage = $product->images->firstWhere('is_default', true);
                }
            }

            if (!$targetDefaultImage) {
                $targetDefaultImage = $product->images->first();
            }

            if ($targetDefaultImage) {
                foreach ($product->images as $img) {
                    $img->update(['is_default' => ($img->id === $targetDefaultImage->id)]);
                }
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Product is updated successfully.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }
        $product->images()->delete();
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product is deleted successfully.');
    }

    public function show(Product $product)
    {
        $relations = ['category', 'manufacturer', 'images', 'seller'];
        if (class_exists(\SGCart\ProductVariants\Models\ProductVariant::class)) {
            $relations[] = 'variants.color';
            $relations[] = 'variants.size';
            $relations[] = 'variants.images';
        }
        $product->load($relations);

        if ($product->seller_id && ($product->status === \App\Enums\ProductStatus::PENDING_APPROVAL || $product->status === \App\Enums\ProductStatus::REJECTED)) {
            return view('marketplace::admin.products.show_approval', compact('product'));
        }

        return view('admin.products.show', compact('product'));
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

    public function deleteImage(\App\Models\ProductImage $productImage)
    {
        $productId = $productImage->product_id;
        $wasDefault = $productImage->is_default;

        if ($productImage->image_path) {
            Storage::disk('public')->delete($productImage->image_path);
        }
        $productImage->delete();

        if ($wasDefault) {
            $nextImage = \App\Models\ProductImage::where('product_id', $productId)->first();
            if ($nextImage) {
                $nextImage->update(['is_default' => true]);
            }
        }

        return redirect()->back()->with('success', 'Product image is deleted successfully.');
    }

    /**
     * Add a search tag manually.
     */
    public function addSearchTag(Request $request, Product $product)
    {
        $request->validate([
            'term' => 'required|string|max:100',
        ]);

        $term = strtolower(trim($request->input('term')));

        // Check if it already exists for this product
        $exists = $product->searchTerms()->where('term', $term)->exists();
        if (!$exists) {
            $product->searchTerms()->create(['term' => $term]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Search tag added successfully.']);
        }

        return back()->with('success', 'Search tag added successfully.');
    }

    /**
     * Delete a search tag.
     */
    public function deleteSearchTag(Product $product, $tagId)
    {
        $tag = $product->searchTerms()->findOrFail($tagId);
        $tag->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Search tag deleted successfully.']);
        }

        return back()->with('success', 'Search tag deleted successfully.');
    }

    /**
     * Generate search tags automatically from name, description, and category.
     */
    public function generateSearchTags(Product $product)
    {
        $tags = \App\Services\SearchTagGenerator::generate($product);

        $addedCount = 0;
        foreach ($tags as $tag) {
            $exists = $product->searchTerms()->where('term', $tag)->exists();
            if (!$exists) {
                $product->searchTerms()->create(['term' => $tag]);
                $addedCount++;
            }
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Generated search tags. Added {$addedCount} new tags.",
                'tags' => $product->searchTerms()->get(['id', 'term'])
            ]);
        }

        return back()->with('success', "Generated search tags. Added {$addedCount} new tags.");
    }

    /**
     * Fetch search tags in JSON format.
     */
    public function getSearchTagsJson(Product $product)
    {
        return response()->json([
            'tags' => $product->searchTerms()->get(['id', 'term'])
        ]);
    }
}