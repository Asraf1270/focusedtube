<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\Category\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories)
    {
    }

    public function index(Request $request): View
    {
        $query = Category::query()->withCount('videos')->orderBy('sort_order')->orderBy('name');

        if ($search = trim((string) $request->input('q'))) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($status = $request->input('status')) {
            if (in_array($status, [Category::STATUS_ACTIVE, Category::STATUS_INACTIVE], true)) {
                $query->where('status', $status);
            }
        }

        return view('admin.categories.index', [
            'categories' => $query->paginate(20)->withQueryString(),
            'filters'    => [
                'q'      => $request->input('q', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', [
            'category' => new Category([
                'status'     => Category::STATUS_ACTIVE,
                'sort_order' => (int) Category::query()->max('sort_order') + 1,
            ]),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $category = $this->categories->create($request->validated(), $request->user());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', "Category \"{$category->name}\" created.");
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('admin.categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categories->update($category, $request->validated(), $request->user());

        return redirect()
            ->route('admin.categories.edit', $category)
            ->with('status', "Category \"{$category->name}\" updated.");
    }

    public function destroy(Category $category, Request $request): RedirectResponse
    {
        $this->authorize('delete', $category);

        $name = $category->name;

        $this->categories->delete($category, $request->user());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', "Category \"{$name}\" deleted. Its videos are now uncategorized.");
    }
}