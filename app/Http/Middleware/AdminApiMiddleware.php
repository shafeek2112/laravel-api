<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Enums\UserIsAdmin;

class AdminApiMiddleware
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
        if ($request->user()['is_admin'] === UserIsAdmin::USER_IS_ADMIN) {
            return $next($request);
        } else {
            return response()->json([
                'status'	=> 'Error', 
                'message' 	=> 'You are not Admin to do this action.', 
                'data' 		=> [],
                'code'		=> 403
            ]);
        }
    }
}
