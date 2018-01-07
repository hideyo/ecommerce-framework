<?php

namespace Hideyo\Ecommerce\Framework;

use Illuminate\Support\ServiceProvider;

use Cviebrock\EloquentSluggable\ServiceProvider as SluggableServiceProvider;
use hisorange\BrowserDetect\Provider\BrowserDetectService;
use Collective\Html\HtmlServiceProvider;
use Hideyo\Ecommerce\Framework\Services\HtmlServiceProvider as CustomHtmlServiceProvider;
use Krucas\Notification\NotificationServiceProvider;
use Yajra\Datatables\DatatablesServiceProvider;
use Felixkiss\UniqueWithValidator\UniqueWithValidatorServiceProvider;
use Auth;
use Schema;

class FrameworkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(\Illuminate\Routing\Router $router)
    { 

        Schema::defaultStringLength(191);

        $router->middlewareGroup('hideyobackend', array(
                \App\Http\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \App\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Krucas\Notification\Middleware\NotificationMiddleware::class
            )
        );
    }
    
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->mergeConfig();

        $this->registerRequiredProviders();

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\BrandRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\BrandRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\BlogRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\BlogRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\RedirectRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\RedirectRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ProductCombinationRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ProductCombinationRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\AttributeGroupRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\AttributeGroupRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\AttributeRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\AttributeRepository'
        );


        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\LanguageRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\LanguageRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\UserRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\UserRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\RoleRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\RoleRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ProductRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ProductRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ProductRelatedProductRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ProductRelatedProductRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ProductExtraFieldValueRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ProductExtraFieldValueRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ExtraFieldDefaultValueRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ExtraFieldDefaultValueRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ExtraFieldRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ExtraFieldRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\CouponRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\CouponRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ClientRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ClientRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ClientAddressRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ClientAddressRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ProductCategoryRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ProductCategoryRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ShopRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ShopRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\UserLogRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\UserLogRepository'
        );


        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ProductAmountOptionRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ProductAmountOptionRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ProductAmountSeriesRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ProductAmountSeriesRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ProductTagGroupRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ProductTagGroupRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ProductWaitingListRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ProductWaitingListRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\TaxRateRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\TaxRateRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\PaymentMethodRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\PaymentMethodRepository'
        );


        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\SendingMethodRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\SendingMethodRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\OrderRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\OrderRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\OrderAddressRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\OrderAddressRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\OrderPaymentLogRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\OrderPaymentLogRepository'
        );


        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\OrderStatusRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\OrderStatusRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\OrderStatusEmailTemplateRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\OrderStatusEmailTemplateRepository'
        );


        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\InvoiceRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\InvoiceRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\InvoiceAddressRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\InvoiceAddressRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\SendingPaymentMethodRelatedRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\SendingPaymentMethodRelatedRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\RecipeRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\RecipeRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\NewsRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\NewsRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ContentRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ContentRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\FaqItemRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\FaqItemRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\HtmlBlockRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\HtmlBlockRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\GeneralSettingRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\GeneralSettingRepository'
        );

        $this->app->bind(
            'Hideyo\Ecommerce\Framework\Repositories\ExceptionRepositoryInterface',
            'Hideyo\Ecommerce\Framework\Repositories\ExceptionRepository'
        );

    }

    /**
     * Register 3rd party providers.
     */
    protected function registerRequiredProviders()
    {
        // $this->app->register(SluggableServiceProvider::class);
        // $this->app->register(HtmlServiceProvider::class);
        // $this->app->register(NotificationServiceProvider::class);
        // $this->app->register(DatatablesServiceProvider::class);
        // $this->app->register(CustomHtmlServiceProvider::class);
        // $this->app->register(UniqueWithValidatorServiceProvider::class);

        // if (class_exists('Illuminate\Foundation\AliasLoader')) {
        //     $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        //     $loader->alias('Form', \Collective\Html\FormFacade::class);
        //     $loader->alias('Html', \Collective\Html\HtmlFacade::class);
        //     $loader->alias('Notification', \Krucas\Notification\Facades\Notification::class);
        // }
    }

}
