<?php

namespace Hideyo\Ecommerce\Framework\Providers;

use Illuminate\Support\ServiceProvider;

use Hideyo\Ecommerce\Framework\Repositories\ShopRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ShopRepository;
use Hideyo\Ecommerce\Framework\Repositories\ProductCategoryRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ProductCategoryRepository;
use Hideyo\Ecommerce\Framework\Repositories\BrandRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\BrandRepository;
use Hideyo\Ecommerce\Framework\Repositories\ProductRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ProductRepository;
use Hideyo\Ecommerce\Framework\Repositories\ProductTagGroupRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ProductTagGroupRepository;
use Hideyo\Ecommerce\Framework\Repositories\ExtraFieldRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ExtraFieldRepository;
use Hideyo\Ecommerce\Framework\Repositories\AttributeGroupRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\AttributeGroupRepository;
use Hideyo\Ecommerce\Framework\Repositories\AttributeRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\AttributeRepository;
use Hideyo\Ecommerce\Framework\Repositories\ProductCombinationRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ProductCombinationRepository;
use Hideyo\Ecommerce\Framework\Repositories\ProductAmountOptionRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ProductAmountOptionRepository;
use Hideyo\Ecommerce\Framework\Repositories\ProductAmountSeriesRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ProductAmountSeriesRepository;
use Hideyo\Ecommerce\Framework\Repositories\ProductRelatedProductRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ProductRelatedProductRepository;
use Hideyo\Ecommerce\Framework\Repositories\RedirectRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\RedirectRepository;
use Hideyo\Ecommerce\Framework\Repositories\ProductExtraFieldValueRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ProductExtraFieldValueRepository;
use Hideyo\Ecommerce\Framework\Repositories\OrderRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\OrderRepository;
use Hideyo\Ecommerce\Framework\Repositories\OrderAddressRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\OrderAddressRepository;
use Hideyo\Ecommerce\Framework\Repositories\OrderStatusRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\OrderStatusRepository;
use Hideyo\Ecommerce\Framework\Repositories\OrderStatusEmailTemplateRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\OrderStatusEmailTemplateRepository;
use Hideyo\Ecommerce\Framework\Repositories\CartRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\CartRepository;
use Hideyo\Ecommerce\Framework\Repositories\InvoiceRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\InvoiceRepository;
use Hideyo\Ecommerce\Framework\Repositories\InvoiceAddressRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\InvoiceAddressRepository;
use Hideyo\Ecommerce\Framework\Repositories\SendingMethodRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\SendingMethodRepository;
use Hideyo\Ecommerce\Framework\Repositories\SendingPaymentMethodRelatedRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\SendingPaymentMethodRelatedRepository;
use Hideyo\Ecommerce\Framework\Repositories\PaymentMethodRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\PaymentMethodRepository;
use Hideyo\Ecommerce\Framework\Repositories\CouponRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\CouponRepository;
use Hideyo\Ecommerce\Framework\Repositories\TaxRateRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\TaxRateRepository;
use Hideyo\Ecommerce\Framework\Repositories\GeneralSettingRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\GeneralSettingRepository;
use Hideyo\Ecommerce\Framework\Repositories\ClientRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ClientRepository;
use Hideyo\Ecommerce\Framework\Repositories\ClientAddressRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ClientAddressRepository;
use Hideyo\Ecommerce\Framework\Repositories\NewsRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\NewsRepository;
use Hideyo\Ecommerce\Framework\Repositories\ContentRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ContentRepository;
use Hideyo\Ecommerce\Framework\Repositories\HtmlBlockRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\HtmlBlockRepository;
use Hideyo\Ecommerce\Framework\Repositories\FaqItemRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\FaqItemRepository;
use Hideyo\Ecommerce\Framework\Repositories\UserRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\UserRepository;
use Hideyo\Ecommerce\Framework\Repositories\ExceptionRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ExceptionRepository;

class RepositoryServiceProvider extends ServiceProvider {
    
    /**
     * Note: please keep logic in this repository. Put logic not in models,
     * Information about models in Laravel: http://laravel.com/docs/5.1/eloquent
     * @author     Matthijs Neijenhuijs <matthijs@dutchbridge.nl>
     * @copyright  DutchBridge - dont share/steel!
     */
    
    public function register()
    {
        $this->app->singleton(ShopRepositsoryInterface::class, ShopRepository::class);
        $this->app->singleton(ProductCombinationRepositoryInterface::class, ProductCombinationRepository::class);
        $this->app->singleton(AttributeRepositoryInterface::class, AttributeRepository::class);
        $this->app->singleton(AttributeGroupRepositoryInterface::class, AttributeGroupRepository::class);
        $this->app->singleton(LanguageRepositoryInterface::class, LanguageRepository::class);
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);
        $this->app->singleton(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->singleton(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->singleton(ProductImageRepositoryInterface::class, ProductImageRepository::class);
        $this->app->singleton(ProductRelatedProductRepositoryInterface::class, ProductRelatedProductRepository::class);
        $this->app->singleton(ProductExtraFieldValueRepositoryInterface::class, ProductExtraFieldValueRepository::class);
        $this->app->singleton(ProductVariationRepositoryInterface::class, ProductVariationRepository::class);
        $this->app->singleton(ExtraFieldRepositoryInterface::class, ExtraFieldRepository::class);
        $this->app->singleton(ExtraFieldDefaultValueRepositoryInterface::class, ExtraFieldDefaultValueRepository::class);
        $this->app->singleton(ExceptionRepositoryInterface::class, ExceptionRepository::class);
        $this->app->singleton(CouponRepositoryInterface::class, CouponRepository::class);
        $this->app->singleton(GiftVoucherRepositoryInterface::class, GiftVoucherRepository::class);
        $this->app->singleton(DiscountRepositoryInterface::class, DiscountRepository::class);
        $this->app->singleton(CouponGroupRepositoryInterface::class, CouponGroupRepository::class);
        $this->app->singleton(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->singleton(WholesaleClientRepositoryInterface::class, WholesaleClientRepository::class);
        $this->app->singleton(ClientAddressRepositoryInterface::class, ClientAddressRepository::class);
        $this->app->singleton(WholesaleClientAddressRepositoryInterface::class, WholesaleClientAddressRepository::class);
        $this->app->singleton(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->singleton(ProductCategoryRepositoryInterface::class, ProductCategoryRepository::class);
        $this->app->singleton(ContentRepositoryInterface::class, ContentRepository::class);
        $this->app->singleton(ContentImageRepositoryInterface::class, ContentImageRepository::class);
        $this->app->singleton(HtmlBlockRepositoryInterface::class, HtmlBlockRepository::class);
        $this->app->singleton(ShopRepositoryInterface::class, ShopRepository::class);
        $this->app->singleton(UserLogRepositoryInterface::class, UserLogRepository::class);
        $this->app->singleton(ProductCategoryImageRepositoryInterface::class, ProductCategoryImageRepository::class);
        $this->app->singleton(TaxRateRepositoryInterface::class, TaxRateRepository::class);
        $this->app->singleton(ProductVariationTypeRepositoryInterface::class, ProductVariationTypeRepository::class);
        $this->app->singleton(PaymentMethodRepositoryInterface::class, PaymentMethodRepository::class);
        $this->app->singleton(SendingMethodRepositoryInterface::class, SendingMethodRepository::class);
        $this->app->singleton(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->singleton(OrderAddressRepositoryInterface::class, OrderAddressRepository::class);
        $this->app->singleton(OrderStatusEmailTemplateRepositoryInterface::class, OrderStatusEmailTemplateRepository::class);
        $this->app->singleton(OrderStatusRepositoryInterface::class, OrderStatusRepository::class);
        $this->app->singleton(OrderPaymentLogRepositoryInterface::class, OrderPaymentLogRepository::class);
        $this->app->singleton(CartRepositoryInterface::class, CartRepository::class);
        $this->app->singleton(SendingPaymentMethodRelatedRepositoryInterface::class, SendingPaymentMethodRelatedRepository::class);
        $this->app->singleton(CollectionRepositoryInterface::class, CollectionRepository::class);
        $this->app->singleton(RedirectRepositoryInterface::class, RedirectRepository::class);
        $this->app->singleton(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->singleton(InvoiceAddressRepositoryInterface::class, InvoiceAddressRepository::class);
        $this->app->singleton(ProductAmountOptionRepositoryInterface::class, ProductAmountOptionRepository::class);
        $this->app->singleton(ProductAmountSeriesRepositoryInterface::class, ProductAmountSeriesRepository::class);
        $this->app->singleton(GeneralSettingRepositoryInterface::class, GeneralSettingRepository::class);
        $this->app->singleton(FaqItemRepositoryInterface::class, FaqItemRepository::class);
        $this->app->singleton(BrandRepositoryInterface::class, BrandRepository::class);
        $this->app->singleton(NewsRepositoryInterface::class, NewsRepository::class);
        $this->app->singleton(ProductTagGroupRepositoryInterface::class, ProductTagGroupRepository::class);
        $this->app->singleton(ExceptionRepositoryInterface::class, ExceptionRepository::class);

    }
}