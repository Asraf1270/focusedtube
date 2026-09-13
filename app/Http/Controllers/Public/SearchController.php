<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $term       = trim((string) $request->input('q', ''));
        $categoryId = $request->integer('category');
        $sort       = $request->input('sort', 'recent');

        $videos = Video::query()->visibleToUsers();

        if ($term !== '') {
            $videos->search($term);
        }

        if ($categoryId) {
            $videos->where('category_id', $categoryId);
        }

        match ($sort) {
            'popular' => $videos->orderByDesc('views_count')->orderByDesc('published_at'),
            'oldest'  => $videos->orderBy('published_at'),
            default   => $videos->orderByDesc('published_at'),
        };

        return view('pages.search', [
            'term'       => $term,
            'sort'       => $sort,
            'categoryId' => $categoryId,
            'categories' => Category::query()->active()->ordered()->get(['id', 'name']),
            'videos'     => $videos->paginate(24)->withQueryString(),
        ]);
    }
}