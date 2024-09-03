<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthCheck
{
    
    public function handle(Request $request, Closure $next)
    {
        
        if(Auth::guard('admin')->check()){
            return $next($request);
        }
        else{
            return response()->json([
                'code'=>'3',
                'reason' => 'unauthenticated',
                'user'=>Auth::guard('admin')->check()
                ], 422);
        }

    }
}
