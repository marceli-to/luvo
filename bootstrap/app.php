<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\RedirectIfAuthenticated;
use ChinLeung\MultilingualRoutes\DetectRequestLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(DetectRequestLocale::class);

        $middleware->statefulApi();
        $middleware->api(append: ['throttle:200,1']);

        $middleware->validateCsrfTokens(except: [
            'api/image/upload',
            'api/file/upload',
        ]);

        $middleware->alias([
            'guest' => RedirectIfAuthenticated::class,
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
