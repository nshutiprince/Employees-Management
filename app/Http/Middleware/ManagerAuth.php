<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth()->check()) {
            if (Auth()->user()->position == "MANAGER") {
                return $next($request);
            }
            return response()->json(['message' => 'you must be a manager to access this resource', 'employee' => Auth::user()], 400);
        } else {
            return response()->json(['message' => 'you must be loggedin to access this resource'], 400);
        }
    }
}
