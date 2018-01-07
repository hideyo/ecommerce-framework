<?php namespace Hideyo\Ecommerce\Framework\Providers;

use View;
use Illuminate\Support\ServiceProvider;


class ComposerServiceProvider extends ServiceProvider
{

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        // Using class based composers...

        View::composer('*', 'Hideyo\Ecommerce\Framework\ViewComposers\FooterComposer');
        View::composer('*', 'Hideyo\Ecommerce\Framework\ViewComposers\ProductCategoryComposer');
        View::composer('*', 'Hideyo\Ecommerce\Framework\ViewComposers\ShopComposer');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
