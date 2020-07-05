<?php namespace Hideyo\Ecommerce\Framework\Middleware;

use Closure;
use Hideyo\Ecommerce\Framework\Services\Shop\ShopFacade as ShopService;
use Hideyo\Ecommerce\Framework\Services\News\NewsFacade as NewsService;
use Hideyo\Ecommerce\Framework\Services\ProductCategory\ProductCategoryFacade as ProductCategoryService;


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
        $shop = ShopService::findUrl($request->root());

        if(!$shop) {
            abort(404, "shop cannot be found");
        }

        config()->set('app.url', $request->root());
        config()->set('app.shop_id', $shop->id);
        view()->share('shop', $shop);
        app()->instance('shop', $shop);


        view()->share('footerNews', NewsService::selectByLimitAndOrderBy(config()->get('app.shop_id'), '5', 'desc'));

        view()->share('frontendProductCategories', ProductCategoryService::selectAllByShopIdAndRoot());

        return $next($request);
    }
}