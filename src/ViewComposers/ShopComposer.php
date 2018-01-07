<?php namespace Hideyo\Ecommerce\Framework\ViewComposers;

use Illuminate\Contracts\View\View;

use App\Shop;
use Auth;
use Request;
use Hideyo\Ecommerce\Framework\Repositories\ShopRepositoryInterface;
use Config;

class ShopComposer
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ShopRepositoryInterface $shop)
    {
        $this->shop = $shop;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $shop = $this->shop->find(Config::get('app.shop_id'));
        $view->with('shopFrontend', $shop);
    }
}
