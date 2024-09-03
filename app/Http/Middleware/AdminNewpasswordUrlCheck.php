<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminNewpasswordUrlCheck
{
    #check if the admin password reset url have the kln get variable
    
    public function handle(Request $request, Closure $next)
    {
        if (!$request->has('kln')) {
                return response()->json([
                    'code'=>'201',
                    'reason' => 'invalid reset link',
                ], 422);
        }

        return $next($request);
    }
}
