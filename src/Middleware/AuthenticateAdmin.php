<?php

namespace Hideyo\Ecommerce\Framework\Middleware;

use Closure;
use Hideyo\Ecommerce\Framework\Models\Shop as Shop;

class AuthenticateAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (auth()->guard('hideyobackend')->guest()) {
            if ($request->ajax()) {
                return response('Unauthorized.', 401);
            } 
            
            return redirect()->guest('/admin/security/login');
        }

        if (auth()->guard('hideyobackend')->check()) {
            view()->share('this_user', auth()->guard('hideyobackend')->user());
            view()->share('available_shops', Shop::all());
        }

        return $next($request);
    }
}