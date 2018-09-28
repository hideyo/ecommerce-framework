<?php
namespace Hideyo\Ecommerce\Framework\Repositories;
 
use Hideyo\Ecommerce\Framework\Models\Shop;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Cart;
use Hideyo\Ecommerce\Framework\Repositories\SendingMethodRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\PaymentMethodRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ShopRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\ProductRepositoryInterface;
use Hideyo\Ecommerce\Framework\Repositories\CouponRepositoryInterface;
use Hideyo\Ecommerce\Framework\Models\ProductAttribute;
 
class CartRepository implements CartRepositoryInterface 
{
    protected $cart;

    public function __construct(
        Cart $cart, 
        SendingMethodRepositoryInterface $sendingMethod, 
        PaymentMethodRepositoryInterface $paymentMethod, 
        ProductRepositoryInterface $product,
        CouponRepositoryInterface $coupon,
        Shop $shop)
    {
        $this->cart = $cart;
        $this->sendingMethod = $sendingMethod;
        $this->paymentMethod = $paymentMethod;
        $this->product = $product;
        $this->shop = $shop;
        $this->coupon = $coupon;
    }

    public function updateAmountProduct($productId, $amount, $leadingAttributeId, $productAttributeId)
    {
        $explode = explode('-', $productId);
        $product = $this->product->selectOneById($explode[0]);

        $productCombination = false;
        if (isset($explode[1])) {
            $productAttributeId = $explode[1];
            $productCombination = ProductAttribute::where('id', '=', $productAttributeId)->first();
        }

        if ($product->id) {
                $productArray = $product->toArray();
                $productArray['product_amount_series'] = false;
                $productArray['product_category_slug'] = $product->productCategory->slug;
                $productArray['price_details'] = $product->getPriceDetails();
            if ($productCombination) {
                $productArray['id'] = $productArray['id'].'-'.$productCombination->id;
                $productArray['product_id'] = $product->id;
                $productArray['price_details'] = $productCombination->getPriceDetails();

                $productArray['product_combination_title'] = array();
                $productArray['attributeIds'] = $productCombination->combinations->pluck('attribute_id')->toArray();
                foreach ($productCombination->combinations as $combination) {
                    $productArray['product_combination_title'][$combination->attribute->attributeGroup->title] = $combination->attribute->value;
                }
    
                $productArray['product_combination_id'] = $productCombination->id;
                if ($productCombination->price) {
                    $productArray['price_details'] = $productCombination->getPriceDetails();
                }

                if ($productCombination->reference_code) {
                    $productArray['reference_code'] = $productCombination->reference_code;
                }

                $productArray['product_images'] =     $this->product->ajaxProductImages($product, array($leadingAttributeId), $productAttributeId);
            }

            if ($product->amountSeries()->where('active', '=', '1')->count()) {
                $productArray['product_amount_series'] = true;
                $productArray['product_amount_series_range'] = $product->amountSeries()->where('active', '=', '1')->first()->range();
            }

            if($productArray['price_details']['amount'] > 0) {

                if($amount >= $productArray['price_details']['amount']) {
                    $amount = $productArray['price_details']['amount'];
                }

                Cart::update($productId, array(
                  'quantity' => array(
                      'relative' => false,
                      'value' => $amount
                  ),
                ));
            } else {
                Cart::remove($productId);
            }

            if(Cart::getConditionsByType('sending_method_country_price')->count()) {
                $this->updateSendingMethodCountryPrice(Cart::getConditionsByType('sending_method_country_price')->first()->getAttributes()['data']['sending_method_country_price_id']);  
            }
        }
    }
   
    public function postProduct($productId, $productCombinationId = false, $leadingAttributeId, $productAttributeId, $amount)
    {
        $product = $this->product->selectOneByShopIdAndId(config()->get('app.shop_id'), $productId);
        $productCombination = false;

        if ($productCombinationId) {
            $productCombination = ProductAttribute::where('id', '=', $productCombinationId)->first();
        } elseif ($product->attributes()->count()) {
            return false;
        }
 
        if ($product->id) {
            $productArray = $product->toArray();
            $productArray['product_amount_series'] = false;
            $productArray['product_category_slug'] = $product->productCategory->slug;
            $productArray['tax_rate'] = $product->taxRate->rate;

            $productArray['price_details'] = $product->getPriceDetails();
            if ($productCombination) {
                $productArray['product_combination_title'] = array();
                $productArray['attributeIds'] = $productCombination->combinations->pluck('attribute_id')->toArray();
                foreach ($productCombination->combinations as $combination) {
                    $productArray['product_combination_title'][$combination->attribute->attributeGroup->title] = $combination->attribute->value;
                }
    
                $productArray['product_combination_id'] = $productCombination->id;
                if ($productCombination->price) {
                    $productArray['price_details'] = $productCombination->getPriceDetails();
                }

                if ($productCombination->reference_code) {
                    $productArray['reference_code'] = $productCombination->reference_code;
                }

                $productArray['product_images'] =     $this->product->ajaxProductImages($product, array($leadingAttributeId, $productAttributeId));
            }

            $productId = $productArray['id'];
   
            if (isset($productArray['product_combination_id'])) {
                $productId = $productArray['id'].'-'.$productArray['product_combination_id'];
            }

            $discountValue = false;

            if(session()->get('preSaleDiscount')) {
                $preSaleDiscount = session()->get('preSaleDiscount');             



                if ($preSaleDiscount['value'] AND $preSaleDiscount['collection_id'] == $product->collection_id) {

                    if ($preSaleDiscount['discount_way'] == 'amount') {
                        $discountValue = "-".$preSaleDiscount->value;
                      } elseif ($preSaleDiscount['discount_way'] == 'percent') {          
                        $discountValue = "-".$preSaleDiscount['value']."%";                    
                    }                     
                }

                if($preSaleDiscount['products']) {

                    $productIds = array_column($preSaleDiscount['products'], 'id');

                    if (in_array($product->id, $productIds) OR (isset($product->product_id) AND in_array($product->product_id, $productIds))) {

                        if ($preSaleDiscount['discount_way'] == 'amount') {
                            $discountValue = "-".$preSaleDiscount->value;
                        } elseif ($preSaleDiscount['discount_way'] == 'percent') {
                            $discountValue = "-".$preSaleDiscount['value']."%";                     
                        }
                    }

                }             

            }

            if ($product->discount_value) {
                if ($product->discount_type == 'amount') {
                    $discountValue = "-".$product->discount_value;
                } elseif ($product->discount_type == 'percent') {
                    $discountValue = "-".$product->discount_value."%"; 
                }
            }

            $discountCondition = array();
            if($discountValue) {

                $discountCondition = new \Hideyo\Ecommerce\Framework\Services\Cart\CartCondition(array(
                    'name' => 'Discount',
                    'type' => 'tax',
                    'target' => 'item',
                    'value' => $discountValue,
                ));
            }

            return Cart::add($productId, $productArray,  $amount, $discountCondition);
        }

        return false;
    }

    public function updateSendingMethod($sendingMethodId)
    {
        $sendingMethod = $this->sendingMethod->selectOneByShopIdAndId(config()->get('app.shop_id'), $sendingMethodId);
        $sendingMethodArray = array();
        if (isset($sendingMethod->id)) {
            $sendingMethodArray = $sendingMethod->toArray();          
            $sendingMethodArray['price_details'] = $sendingMethod->getPriceDetails();
            if($sendingMethod->relatedPaymentMethodsActive) {
                $sendingMethodArray['related_payment_methods_list'] = $sendingMethod->relatedPaymentMethodsActive->pluck('title', 'id');                
            }

        }

        Cart::removeConditionsByType('sending_method');
        $condition = new \Hideyo\Ecommerce\Framework\Services\Cart\CartCondition(array(
            'name' => 'Sending method',
            'type' => 'sending_method',
            'target' => 'subtotal',
            'value' => 0,
            'attributes' => array(
                'data' => $sendingMethodArray
            )
        ));

        Cart::condition($condition);

        if (!Cart::getConditionsByType('payment_method')->count() and $sendingMethod->relatedPaymentMethodsActive) {
            $this->updatePaymentMethod($sendingMethod->relatedPaymentMethodsActive->first()->id);
        }

        return true;
    }

    public function updatePaymentMethod($paymentMethodId)
    {
        $paymentMethod = $this->paymentMethod->selectOneByShopIdAndId(config()->get('app.shop_id'), $paymentMethodId);

        $paymentMethodArray = array();
        if (isset($paymentMethod->id)) {
            $paymentMethodArray = $paymentMethod->toArray();
            $paymentMethodArray['price_details'] = $paymentMethod->getPriceDetails();
        }

        $valueExTax = $paymentMethodArray['price_details']['original_price_ex_tax'];
        $valueIncTax = $paymentMethodArray['price_details']['original_price_inc_tax'];
        $shop = ShopService::find(config()->get('app.shop_id'));
        $value = $valueIncTax;
        $freeSending = ( $paymentMethodArray['no_price_from'] - Cart::getSubTotalWithTax());


        if ($freeSending < 0) {
            $value = 0;
            $valueIncTax = 0;
            $valueExTax = 0;
        }

        $paymentMethodArray['value_inc_tax'] = $valueIncTax;
        $paymentMethodArray['value_ex_tax'] = $valueExTax;

        Cart::removeConditionsByType('payment_method');
        $condition = new \Hideyo\Ecommerce\Framework\Services\Cart\CartCondition(array(
            'name' => 'Payment method',
            'type' => 'payment_method',
            'target' => 'subtotal',
            'value' => $value,
            'attributes' => array(
                'data' => $paymentMethodArray
            )
        ));

        return Cart::condition($condition);
    }

    public function updateSendingMethodCountryPrice($sendingMethodCountryPriceId)
    {
        $sendingMethodCountryPrice = $this->sendingMethod->selectOneCountryPriceByShopIdAndId(config()->get('app.shop_id'), $sendingMethodCountryPriceId);
     
        if ($sendingMethodCountryPrice) {
            $sendingMethod = $sendingMethodCountryPrice->sendingMethod;
            $sendingMethodArray = array();
            if (isset($sendingMethod->id)) {
                $sendingMethodArray = $sendingMethodCountryPrice->toArray();
                if ($sendingMethod->countryPrices()->count()) {
                    $sendingMethodArray['countries'] = $sendingMethod->countryPrices->toArray();
                    $sendingMethodArray['country_list'] = $sendingMethod->countryPrices->pluck('name', 'id');
                }
                $sendingMethodArray['sending_method_country_price'] = $sendingMethodCountryPrice;
                $sendingMethodArray['sending_method_country_price_id'] = $sendingMethodCountryPrice->id;
                $sendingMethodArray['sending_method_country_price_country_code'] = $sendingMethodCountryPrice->country_code;
                $sendingMethodArray['no_price_from'] = $sendingMethodCountryPrice->no_price_from;
                
                $sendingMethodArray['price_details'] = $sendingMethodCountryPrice->getPriceDetails();
                $sendingMethodArray['sending_method_country_price'] = $sendingMethodCountryPrice->toArray();
            }

            $shop = ShopService::find(config()->get('app.shop_id'));

            $valueExTax = $sendingMethodArray['price_details']['original_price_ex_tax'];
            $valueIncTax = $sendingMethodArray['price_details']['original_price_inc_tax'];
            $value = $valueIncTax;
            $freeSending = ( $sendingMethodArray['no_price_from'] - Cart::getSubTotalWithTax());
      
            if ($freeSending < 0) {
                $value = 0;
                $valueIncTax = 0;
                $valueExTax = 0;
            }

            $sendingMethodArray['value_inc_tax'] = $valueIncTax;
            $sendingMethodArray['value_ex_tax'] = $valueExTax;

            Cart::removeConditionsByType('sending_method_country_price');
            $condition1 = new \Hideyo\Ecommerce\Framework\Services\Cart\CartCondition(array(
                'name' => 'Sending method country price',
                'type' => 'sending_method_country_price',
                'target' => 'subtotal',
                'value' => 0,
                'attributes' => array(
                    'data' => $sendingMethodArray
                )
            ));

            Cart::removeConditionsByType('sending_cost');
            $condition2 = new \Hideyo\Ecommerce\Framework\Services\Cart\CartCondition(array(
                'name' => 'Sending Cost',
                'type' => 'sending_cost',
                'target' => 'subtotal',
                'value' => $value,
                'attributes' => array(
                    'data' => $sendingMethodArray
                )
            ));

            Cart::condition([$condition1, $condition2]);

            if (!Cart::getConditionsByType('payment_method')->count() and $sendingMethod->relatedPaymentMethodsActive->first()->id) {
                $this->updatePaymentMethod($sendingMethod->relatedPaymentMethodsActive->first()->id);
            }

            return true;
        }     
    }

    function updateOrderStatus($orderStatusId) 
    {
        session()->put('orderStatusId', $orderStatusId);
    }

    function addClient($clientId) 
    {
        session()->put('orderClientId', $clientId);
        session()->forget('orderClientBillAddressId');
        session()->forget('orderClientDeliveryAddressId');
    }

    function addClientBillAddress($clientBillAddressId) 
    {
        session()->put('orderClientBillAddressId', $clientBillAddressId);
    }

    function addClientDeliveryAddress($clientDeliveryAddressId) 
    {
        session()->put('orderClientDeliveryAddressId', $clientDeliveryAddressId);
    }

    public function updateCouponCode($couponCode) {

        Cart::removeConditionsByType('coupon');
        $coupon = $this->coupon->selectOneByShopIdAndCode(config()->get('app.shop_id'), $couponCode);
        
        $couponData = array();
        $discountValue = 0;

        if($coupon) {

            $couponData = $coupon->toArray();
            if($coupon->type == 'total_price') {

                if($coupon->discount_way == 'total') {
                    $discountValue = $coupon->value;
                } elseif ($coupon->discount_way == 'percent') {
                    $discountValue = $coupon->value.'%';
                } 

                self::setCouponCode($discountValue, $couponData, $couponCode);
            }

            if($coupon->type == 'product') {

                if($coupon->products()->count()) {
                    
                    foreach (Cart::getContent() as $row) {

                        $id = $row->id;
                        $explode = explode('-', $id);
                        $contains = $coupon->products->contains($explode[0]);

                        if($contains) {

                            if($coupon->discount_way == 'total') {
                                $discountValue += $coupon->value;
                            } elseif ($coupon->discount_way == 'percent') {
                                $value = $coupon->value / 100;                      
                                $discountValue += $row->getOriginalPriceWithTaxSum() * $value;
                            }                             
                        }
                    }

                    self::setCouponCode($discountValue, $couponData, $couponCode);
                }
            }

            if($coupon->type == 'product_category') {

                if($coupon->productCategories()->count()) {

                    foreach (Cart::getContent()->sortBy('id')  as $row) {

                        $contains = $coupon->productCategories->contains($row['attributes']['product_category_id']);

                        if($contains) {

                            if($coupon->discount_way == 'total') {
                                $discountValue += $coupon->value;
                            } elseif ($coupon->discount_way == 'percent') {
                                $value = $coupon->value / 100;                      
                                $discountValue += $row->getOriginalPriceWithTaxSum() * $value;
                            }                             
                        }

                    }

                    self::setCouponCode($discountValue, $couponData, $couponCode);
                }
            }

            if($coupon->type == 'sending_method') {

                if($coupon->sendingMethodCountries()->count()) {

                    foreach ($coupon->sendingMethodCountries as $country) {

                        if(Cart::getConditionsByType('sending_method_country_price')){
                            if($country->name == Cart::getConditionsByType('sending_method_country_price')->first()->getAttributes()['data']['sending_method_country_price']['name']) {

                                if($coupon->discount_way == 'total') {
                                    $discountValue += $coupon->value;
                                } elseif ($coupon->discount_way == 'percent') {
                                    $value = $coupon->value / 100; 
                                    $discountValue += Cart::getConditionsByType('sending_cost')->first()->getValue() * $value;
                                } 
                            }
                        }
                    }

                    self::setCouponCode($discountValue, $couponData, $couponCode);

                } elseif($coupon->sendingMethods()->count()) {

                    foreach ($coupon->sendingMethods as $sendingMethod) {

                        if(Cart::getConditionsByType('sending_cost')){

                            if($sendingMethod->id == Cart::getConditionsByType('sending_cost')->first()->getAttributes()['data']['sending_method']['id']) {

                                if($coupon->discount_way == 'total') {
                                    $discountValue += $coupon->value;
                                } elseif ($coupon->discount_way == 'percent') {
                                    $value = $coupon->value / 100; 
                                    $discountValue += Cart::getConditionsByType('sending_cost')->first()->getValue() * $value;
                                }                 
                            }
                        }            
                    }

                    self::setCouponCode($discountValue, $couponData, $couponCode);
                }
            }

            if($coupon->type == 'payment_method') {

                if($coupon->paymentMethods()->count()) {

                    foreach ($coupon->paymentMethods as $paymentMethod) {

                        if(Cart::getConditionsByType('payment_method')){

                            if($paymentMethod->id == Cart::getConditionsByType('payment_method')->first()->getAttributes()['data']['id']) {

                                if($coupon->discount_way == 'total') {
                                    $discountValue += $coupon->value;
                                } elseif ($coupon->discount_way == 'percent') {
                                    $value = $coupon->value / 100; 
                                    $discountValue += Cart::getConditionsByType('payment_method')->first()->getValue() * $value;
                                }                 
                            }
                        }            
                    }

                    self::setCouponCode($discountValue, $couponData, $couponCode);
                }
            }
        }
    }

    public function setCouponCode($discountValue, $couponData, $couponCode)
    {
        $condition = new \Hideyo\Ecommerce\Framework\Services\Cart\CartCondition(array(
            'name' => 'Coupon code',
            'type' => 'coupon',
            'target' => 'subtotal',
            'value' => '-'.$discountValue,
            'attributes' => array(
                'couponData' => $couponData,
                'inputCouponValue' => $couponCode
            )
        ));

        Cart::condition($condition);
    }

    public function replaceTags($content, $order)
    {
        $number = $order->id;
        $replace = array(
            'orderId' => $order->id,
            'orderCreated' => $order->created_at,
            'orderTotalPriceWithTax' => $order->price_with_tax,
            'orderTotalPriceWithoutTax' => $order->price_without_tax,
            'clientEmail' => $order->client->email,
            'clientFirstname' => $order->orderBillAddress->firstname,
            'clientLastname' => $order->orderBillAddress->lastname,
            'clientDeliveryStreet' => $order->orderDeliveryAddress->street,
            'clientDeliveryHousenumber' => $order->orderDeliveryAddress->housenumber,
            'clientDeliveryHousenumberSuffix' => $order->orderDeliveryAddress->housenumber_suffix,
            'clientDeliveryZipcode' => $order->orderDeliveryAddress->zipcode,
            'clientDeliveryCity' => $order->orderDeliveryAddress->city,
            'clientDeliveryCounty' => $order->orderDeliveryAddress->country,
        );

        foreach ($replace as $key => $val) {
            $content = str_replace("[" . $key . "]", $val, $content);
        }
        $content = nl2br($content);
        return $content;
    }  
}