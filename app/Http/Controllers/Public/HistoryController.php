<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\Watch\WatchHistoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function __construct(
        private readonly WatchHistoryService $history,
    ) {
    }

    public function index(Request $request): View
    {
        return view('pages.history.index', [
            'grouped' => $this->history->groupedFor($request->user()),
        ]);
    }

    public function destroy(Request $request, int $entry): RedirectResponse
    {
        $this->history->removeEntry($request->user(), $entry);

        return back()->with('status', 'Removed from history.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $count = $this->history->clear($request->user());

        return back()->with('status', "Cleared {$count} history entr".($count === 1 ? 'y' : 'ies').'.');
    }
}