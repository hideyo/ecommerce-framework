<?php namespace Hideyo\Ecommerce\Framework\ViewComposers;

use Illuminate\Contracts\View\View;
use Hideyo\Ecommerce\Framework\Services\ProductCategory\ProductCategoryFacade as ProductCategoryService;

class ProductCategoryComposer
{
    /**
     * Bind productcategory data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('frontendProductCategories', ProductCategoryService::selectAllByShopIdAndRoot(config()->get('app.shop_id')));
    }
}
