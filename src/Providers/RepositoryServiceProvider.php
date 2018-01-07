<?php

namespace Hideyo\Ecommerce\Framework\Providers;

use Illuminate\Support\ServiceProvider;

Hideyo\Ecommerce\Framework\Repositories\ShopRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\ShopRepository;
Hideyo\Ecommerce\Framework\Repositories\ProductCategoryRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\ProductCategoryRepository;
Hideyo\Ecommerce\Framework\Repositories\BrandRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\BrandRepository;
Hideyo\Ecommerce\Framework\Repositories\ProductRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\ProductRepository;
Hideyo\Ecommerce\Framework\Repositories\ProductTagGroupRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\ProductTagGroupRepository;
Hideyo\Ecommerce\Framework\Repositories\ExtraFieldRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\ExtraFieldRepository;
Hideyo\Ecommerce\Framework\Repositories\AttributeGroupRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\AttributeGroupRepository;
Hideyo\Ecommerce\Framework\Repositories\AttributeRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\AttributeRepository;
Hideyo\Ecommerce\Framework\Repositories\ProductCombinationRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\ProductCombinationRepository;
Hideyo\Ecommerce\Framework\Repositories\ProductAmountOptionRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\ProductAmountOptionRepository;
Hideyo\Ecommerce\Framework\Repositories\ProductAmountSeriesRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\ProductAmountSeriesRepository;
Hideyo\Ecommerce\Framework\Repositories\ProductRelatedProductRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\ProductRelatedProductRepository;
Hideyo\Ecommerce\Framework\Repositories\RedirectRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\RedirectRepository;
Hideyo\Ecommerce\Framework\Repositories\ProductExtraFieldValueRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\ProductExtraFieldValueRepository;
Hideyo\Ecommerce\Framework\Repositories\OrderRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\OrderRepository;
Hideyo\Ecommerce\Framework\Repositories\OrderAddressRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\OrderAddressRepository;
Hideyo\Ecommerce\Framework\Repositories\OrderStatusRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\OrderStatusRepository;
Hideyo\Ecommerce\Framework\Repositories\OrderStatusEmailTemplateRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\OrderStatusEmailTemplateRepository;
Hideyo\Ecommerce\Framework\Repositories\CartRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\CartRepository;
Hideyo\Ecommerce\Framework\Repositories\InvoiceRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\InvoiceRepository;
Hideyo\Ecommerce\Framework\Repositories\InvoiceAddressRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\InvoiceAddressRepository;
Hideyo\Ecommerce\Framework\Repositories\SendingMethodRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\SendingMethodRepository;
Hideyo\Ecommerce\Framework\Repositories\SendingPaymentMethodRelatedRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\SendingPaymentMethodRelatedRepository;
Hideyo\Ecommerce\Framework\Repositories\PaymentMethodRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\PaymentMethodRepository;
Hideyo\Ecommerce\Framework\Repositories\CouponRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\CouponRepository;
Hideyo\Ecommerce\Framework\Repositories\TaxRateRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\TaxRateRepository;
Hideyo\Ecommerce\Framework\Repositories\GeneralSettingRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\GeneralSettingRepository;
Hideyo\Ecommerce\Framework\Repositories\ClientRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\ClientRepository;
Hideyo\Ecommerce\Framework\Repositories\ClientAddressRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\ClientAddressRepository;
Hideyo\Ecommerce\Framework\Repositories\NewsRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\NewsRepository;
Hideyo\Ecommerce\Framework\Repositories\ContentRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\ContentRepository;
Hideyo\Ecommerce\Framework\Repositories\HtmlBlockRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\HtmlBlockRepository;
Hideyo\Ecommerce\Framework\Repositories\FaqItemRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\FaqItemRepository;
Hideyo\Ecommerce\Framework\Repositories\UserRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\UserRepository;
Hideyo\Ecommerce\Framework\Repositories\ExceptionRepositoryInterface;
Hideyo\Ecommerce\Framework\Repositories\ExceptionRepository;

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