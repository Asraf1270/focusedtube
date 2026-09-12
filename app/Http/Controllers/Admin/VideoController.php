<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    public function index(Request $request): View
    {
        $query = Video::query()
            ->with(['category:id,name', 'creator:id,name'])
            ->latest('id');

        if ($search = trim((string) $request->input('q'))) {
            $query->search($search);
        }

        if ($status = $request->input('status')) {
            if (in_array($status, [Video::STATUS_DRAFT, Video::STATUS_PUBLISHED, Video::STATUS_ARCHIVED], true)) {
                $query->where('status', $status);
            }
        }

        if ($categoryId = $request->integer('category')) {
            $query->where('category_id', $categoryId);
        }

        $videos = $query->paginate(20)->withQueryString();

        return view('admin.videos.index', [
            'videos'     => $videos,
            'categories' => Category::query()->ordered()->get(['id', 'name']),
            'filters'    => [
                'q'        => $request->input('q', ''),
                'status'   => $request->input('status', ''),
                'category' => $request->input('category', ''),
            ],
        ]);
    }
}