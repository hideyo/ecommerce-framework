<?php namespace Hideyo\Ecommerce\Framework\Middleware;

use Closure;
use Hideyo\Ecommerce\Framework\Services\Shop\ShopFacade as ShopService;

class DetectShopDomain
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
        if (config()->get('app.url') != $request->root()) {
            $root = $request->root();
            config()->set('app.url', $root);
        }

        $shop = ShopService::checkByUrl(config()->get('app.url'));

        if(!$shop) {
            abort(404, "shop cannot be found");
        }

        config()->set('app.shop_id', $shop->id);
        view()->share('shop', $shop);
        app()->instance('shop', $shop);

        return $next($request);
    }
}