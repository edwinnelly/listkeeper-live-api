<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureActiveBusiness
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if (!$user || !$user->active_business_key) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'No active business selected.');
        }

        return $next($request);
    }
}
