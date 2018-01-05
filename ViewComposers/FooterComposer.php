<?php namespace Hideyo\ViewComposers;

use Illuminate\Contracts\View\View;
use Hideyo\Repositories\NewsRepositoryInterface;
use Config;

class FooterComposer
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(NewsRepositoryInterface $news)
    {
        $this->news = $news;
    }


    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('footerNews', $this->news->selectByLimitAndOrderBy(Config::get('app.shop_id'), '5', 'desc'));
    }
}
