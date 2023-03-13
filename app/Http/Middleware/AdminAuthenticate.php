<?php

namespace App\Http\Middleware;

use Closure;
use App\AdminToken;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $token = $request->bearerToken();

        $token = AdminToken::where('token', $token)->whereDate('data_expiracao', '<=', \DB::raw('NOW()'))->first();

        if (!$token) {
            return response('Unauthorized.', 401);
        }

        $request->admin = $token->admin;

        return $next($request);
    }
}
