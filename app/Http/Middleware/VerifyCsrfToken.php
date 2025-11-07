<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $addHttpCookie = false;

    protected $except = [
        //
        'http://localhost:80',
        'http://localhost:5173',
        'http://localhost',
        // 'https://localhost',
        // 'http://127.0.0.1:80',
        '/api/*',
        // 'http://localhost:9005/api/swds/*',
    ];
}
