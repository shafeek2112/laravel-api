<?php

namespace App\Http\Middleware;

use App\Enums\UserStatus;
use Closure;
use Illuminate\Http\Request;

class ApiMiddleware
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
        if ($request->user()['status'] === UserStatus::APPROVED) {
            return $next($request);
        } else {
            return response()->json([
                'status'	=> 'Error', 
                'message' 	=> 'You account is not active. Please contact your admin', 
                'data' 		=> [],
                'code'		=> 403
            ]);
        }
    }
}
