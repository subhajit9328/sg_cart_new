<?php

namespace SGCart\Blog\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use SGCart\Blog\Models\BlogPost;
use SGCart\Blog\Models\BlogCategory;

class BlogPostController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogPost::with(['category', 'author']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('blog_category_id', $request->input('category_id'));
        }

        $posts = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $categories = BlogCategory::orderBy('name')->get();

        return view('blog::posts.index', compact('posts', 'categories'));
    }

    public function create()
    {
        $categories = BlogCategory::active()->orderBy('name')->get();
        $products = \App\Models\Product::orderBy('name')->get();
        return view('blog::posts.create', compact('categories', 'products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'summary' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Ensure unique slug
        $originalSlug = $data['slug'];
        $count = 1;
        while (BlogPost::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $count++;
        }

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('blog', 'public');
        }

        $data['user_id'] = auth()->id();

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $post = BlogPost::create($data);
        $post->products()->sync($request->input('products', []));

        return redirect()->route('admin.blog-posts.index')
            ->with('success', 'Blog post created successfully.');
    }

    public function show(Request $request, $id)
    {
        return redirect()->route('admin.blog-posts.index');
    }

    public function edit($id)
    {
        $post = BlogPost::with('products')->findOrFail($id);
        $categories = BlogCategory::active()->orderBy('name')->get();
        $products = \App\Models\Product::orderBy('name')->get();
        return view('blog::posts.edit', compact('post', 'categories', 'products'));
    }

    public function update(Request $request, $id)
    {
        $post = BlogPost::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,' . $id,
            'summary' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'blog_category_id' => 'nullable|exists:blog_categories,id',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        } else {
            $data['slug'] = Str::slug($data['slug']);
        }

        // Ensure unique slug
        $originalSlug = $data['slug'];
        $count = 1;
        while (BlogPost::where('slug', $data['slug'])->where('id', '!=', $id)->exists()) {
            $data['slug'] = $originalSlug . '-' . $count++;
        }

        if ($request->hasFile('featured_image')) {
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('blog', 'public');
        }

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = $post->published_at ?? now();
        } elseif ($data['status'] !== 'published') {
            $data['published_at'] = null;
        }

        $post->update($data);
        $post->products()->sync($request->input('products', []));

        return redirect()->route('admin.blog-posts.edit', $post->id)
            ->with('success', 'Blog post updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $post = BlogPost::findOrFail($id);

        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }

        $post->delete();

        return redirect()->route('admin.blog-posts.index')
            ->with('success', 'Blog post deleted successfully.');
    }
}
