<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class UserStatusCheckMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
        public function handle(Request $request, Closure $next): Response
        {
            if (Auth::check() && !in_array((int) Auth::user()->status, [1, 2])) {
                Auth::logout();

                return redirect()->route('login')->with(
                    'warning',
                    'Your account is temporarily deactivated. Please contact your admin.'
                );
            }

            return $next($request);
        }
}
