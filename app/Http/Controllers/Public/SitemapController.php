<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Playlist;
use App\Models\Video;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = collect();

        $urls->push(['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0']);
        $urls->push(['loc' => route('videos.index'), 'changefreq' => 'daily', 'priority' => '0.9']);
        $urls->push(['loc' => route('categories.index'), 'changefreq' => 'weekly', 'priority' => '0.7']);
        $urls->push(['loc' => route('playlists.index'), 'changefreq' => 'weekly', 'priority' => '0.7']);

        Video::query()
            ->visibleToUsers()
            ->orderByDesc('published_at')
            ->limit(5000)
            ->get(['slug', 'updated_at', 'thumbnail_url'])
            ->each(fn ($v) => $urls->push([
                'loc'        => $v->watchUrl(),
                'lastmod'    => $v->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority'   => '0.8',
                'image'      => $v->thumbnail_url,
            ]));

        Category::query()->active()->ordered()->get(['slug', 'updated_at'])->each(fn ($c) => $urls->push([
            'loc'        => route('categories.show', $c),
            'lastmod'    => $c->updated_at?->toAtomString(),
            'changefreq' => 'weekly',
            'priority'   => '0.6',
        ]));

        Playlist::query()->published()->get(['slug', 'updated_at'])->each(fn ($p) => $urls->push([
            'loc'        => route('playlists.show', $p),
            'lastmod'    => $p->updated_at?->toAtomString(),
            'changefreq' => 'weekly',
            'priority'   => '0.6',
        ]));

        $xml  = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}