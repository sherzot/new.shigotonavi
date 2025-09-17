<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;

class CustomSessionGuard
{
    public function handle($request, Closure $next)
    {
        $guard = Auth::getDefaultDriver(); // 現在の警備員を特定する
        $sessionKey = env('SESSION_COOKIE', 'laravel_session') . '_' . $guard;

        // セッションを動的に変更する
        config(['session.cookie' => $sessionKey]);

        return $next($request);
    }
}
