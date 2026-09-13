<?php

use App\Models\Video;

it('returns a valid XML sitemap', function () {
    Video::factory()->published()->create(['title' => 'Included Video']);
    Video::factory()->create(['title' => 'Draft Video']); // excluded

    $res = $this->get('/sitemap.xml');

    $res->assertOk();
    expect($res->headers->get('Content-Type'))->toContain('application/xml');
    expect($res->getContent())->toContain('<urlset');
    expect($res->getContent())->toContain(route('home'));
    expect($res->getContent())->not->toContain('draft-video');
});