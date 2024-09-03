<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
   
    protected function redirectTo($request)
    {
        $request->headers->set('Accept', 'application/json');
        
        if (! $request->expectsJson()) {
            return route('login');
        }

    }
}
