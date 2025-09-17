<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSomething
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->has('key')) {
            return redirect('error');
        }

        return $next($request);
    }
    protected $routeMiddleware = [
        'check.something' => \App\Http\Middleware\CheckSomething::class,
    ];

}
