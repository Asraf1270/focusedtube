<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->ordered()
            ->withCount(['videos' => fn ($q) => $q->visibleToUsers()])
            ->get();

        return view('pages.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function show(Request $request, Category $category): View
    {
        abort_unless($category->status === Category::STATUS_ACTIVE, 404);

        $videos = Video::query()
            ->visibleToUsers()
            ->where('category_id', $category->id)
            ->orderByDesc('published_at')
            ->paginate(24)
            ->withQueryString();

        return view('pages.categories.show', [
            'category' => $category,
            'videos'   => $videos,
        ]);
    }
}