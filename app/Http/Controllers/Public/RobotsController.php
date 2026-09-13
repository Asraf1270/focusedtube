<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RobotsController extends Controller
{
    public function __invoke(): Response|BinaryFileResponse
    {
        if (! app()->environment('production')) {
            return response(
                "User-agent: *\nDisallow: /",
                200,
                ['Content-Type' => 'text/plain']
            );
        }

        return response()->file(public_path('robots.txt'));
    }
}