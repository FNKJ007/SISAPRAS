<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'  => \App\Http\Middleware\AdminMiddleware::class,
            'user'   => \App\Http\Middleware\UserMiddleware::class,
            'bidang' => \App\Http\Middleware\BidangMiddleware::class,
        ]);

        // Percaya header X-Forwarded-* dari proxy lokal (Herd/Valet/ngrok)
        // maupun reverse proxy produksi (Nginx dsb). Tanpa ini, Laravel salah
        // deteksi skema request sebagai http padahal browser akses via https,
        // sehingga route()/url() bikin link http:// -> Chrome anggap mixed
        // content dan munculkan "Insecure download blocked" saat unduh PDF.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
