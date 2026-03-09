<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $expected = env('ERP_SYNC_KEY');
        $token = $request->bearerToken();

        if (!$expected || $token !== $expected) {
            return response()->json(['error' => 'unauthorized'], 401);
        }

        return $next($request);
    }
}

