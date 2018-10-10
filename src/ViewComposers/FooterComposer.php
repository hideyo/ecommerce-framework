<?php namespace Hideyo\Ecommerce\Framework\ViewComposers;

use Illuminate\Contracts\View\View;
use Hideyo\Ecommerce\Framework\Services\News\NewsFacade as NewsService;

class FooterComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('footerNews', NewsService::selectByLimitAndOrderBy(config()->get('app.shop_id'), '5', 'desc'));
    }
}
