<?php

namespace SGCart\Blog\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use SGCart\Blog\Models\BlogPost;
use SGCart\Blog\Models\BlogCategory;
use App\Traits\ApiResponse;

class BlogController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = BlogPost::with(['category', 'author'])->published();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(config('blog.per_page', 9))
            ->withQueryString();

        $categories = BlogCategory::active()
            ->withCount(['posts' => function ($q) {
                $q->published();
            }])
            ->get();

        $recentPosts = BlogPost::published()
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return $this->successResponse([
                'posts' => $posts,
                'categories' => $categories,
                'recent_posts' => $recentPosts,
            ], 'Articles retrieved successfully.');
        }

        return view('blog::store.index', compact('posts', 'categories', 'recentPosts'));
    }

    public function category(Request $request, $slug)
    {
        $category = BlogCategory::where('slug', $slug)->active()->firstOrFail();

        $posts = BlogPost::with(['category', 'author'])
            ->published()
            ->where('blog_category_id', $category->id)
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(config('blog.per_page', 9))
            ->withQueryString();

        $categories = BlogCategory::active()
            ->withCount(['posts' => function ($q) {
                $q->published();
            }])
            ->get();

        $recentPosts = BlogPost::published()
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return $this->successResponse([
                'category' => $category,
                'posts' => $posts,
                'categories' => $categories,
                'recent_posts' => $recentPosts,
            ], 'Category articles retrieved successfully.');
        }

        return view('blog::store.index', compact('category', 'posts', 'categories', 'recentPosts', 'slug'));
    }

    public function show(Request $request, $slug)
    {
        $post = BlogPost::with(['category', 'author', 'products'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $categories = BlogCategory::active()
            ->withCount(['posts' => function ($q) {
                $q->published();
            }])
            ->get();

        $recentPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->orderBy('published_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return $this->successResponse([
                'post' => $post,
                'categories' => $categories,
                'recent_posts' => $recentPosts,
            ], 'Article details retrieved successfully.');
        }

        return view('blog::store.show', compact('post', 'categories', 'recentPosts', 'slug'));
    }
}
