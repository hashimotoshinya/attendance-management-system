<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LoginTypeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $type
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $type)
    {
        $loginType = session('login_type');

        if ($loginType !== $type) {
            return redirect()->route('login')->withErrors(['login' => '権限がありません。']);
        }

        return $next($request);
    }
}