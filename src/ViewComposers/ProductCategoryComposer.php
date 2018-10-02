<?php namespace Hideyo\Ecommerce\Framework\ViewComposers;

use Illuminate\Contracts\View\View;
use Hideyo\Ecommerce\Framework\Repositories\ProductCategoryRepository;
use Config;

class ProductCategoryComposer
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductCategoryRepository $productCategory)
    {
        $this->productCategory = $productCategory;
    }


    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('frontendProductCategories', $this->productCategory->selectAllByShopIdAndRoot(Config::get('app.shop_id')));
    }
}
