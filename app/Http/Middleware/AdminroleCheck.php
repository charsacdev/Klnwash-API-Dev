<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminroleCheck
{
   
    public function handle(Request $request, Closure $next)
    {
        $rolecheck=Auth::user()->role;
        if($rolecheck=='super'){
            return $next($request);
            
        }
        else{
            return response()->json([
                'code'=>'3',
                'reason' => 'unauthorized access',
                ], 422);
            
        }
        
    }
}
