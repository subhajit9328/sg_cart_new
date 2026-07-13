<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::parents();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('children', function ($childQuery) use ($search) {
                      $childQuery->where('name', 'like', "%{$search}%");
                  });
            });

            $query->with(['children' => function ($q) use ($search) {
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere(function ($parentQ) use ($search) {
                             $parentQ->whereHas('parent', function ($pQ) use ($search) {
                                 $pQ->where('name', 'like', "%{$search}%")
                                    ->whereDoesntHave('children', function ($cQ) use ($search) {
                                        $cQ->where('name', 'like', "%{$search}%");
                                    });
                             });
                         });
                })->orderBy('sort_order');
            }]);
        } else {
            $query->with(['children' => function ($q) {
                $q->orderBy('sort_order');
            }]);
        }

        $sortBy = $request->input('sort_by');
        $sortOrder = $request->input('sort_order') ?? $request->input('sort_dir') ?? 'asc';
        $allowedSortFields = ['name', 'is_active', 'sort_order'];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('sort_order');
        }

        $categories = $query->paginate(10)->withQueryString();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        $parents = Category::parents()->active()->get();
        return view('admin.categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            // parent_id is the integer FK — still resolved by integer id internally
            'parent_id'   => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
        ]);

        $data['slug'] = Str::slug($data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category is created successfully.');
    }

    /**
     * Route model binding resolves Category by `ulid` column automatically.
     */
    public function edit(Category $category)
    {
        $parents = Category::parents()->where('id', '!=', $category->id)->get();
        return view('admin.categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name,'. $category->id,
            'parent_id'   => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
        ]);

        $data['slug'] = Str::slug($data['name']);

        if ($request->hasFile('image')) {
            if ($category->image) Storage::disk('public')->delete($category->image);
            $data['image'] = $request->file('image')->store('categories', 'public');
        }
        DB::transaction(function () use ($category, $request, $data) {
            $category->update($data);
            $category->children()->update(['is_active' => $request->boolean('is_active')]);
        });

        return redirect()->route('admin.categories.edit', $category)->with('success', 'Category is updated successfully.');
    }

    public function destroy(Category $category)
    {
        if ($category->image) Storage::disk('public')->delete($category->image);
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category is deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|exists:categories,id',
        ]);

        foreach ($request->input('order') as $index => $id) {
            Category::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Categories reordered successfully.'
        ]);
    }
}
