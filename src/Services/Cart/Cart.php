<?php

//ugly class ever.......

namespace Hideyo\Ecommerce\Framework\Services\Cart;

use Hideyo\Ecommerce\Framework\Services\Cart\Helpers\Helpers;
use Hideyo\Ecommerce\Framework\Services\SendingMethod\SendingMethodFacade as SendingMethodService;
use Hideyo\Ecommerce\Framework\Services\PaymentMethod\PaymentMethodFacade as PaymentMethodService;
use Hideyo\Ecommerce\Framework\Services\Shop\ShopFacade as ShopService;
use Hideyo\Ecommerce\Framework\Services\Product\ProductFacade as ProductService;
use Hideyo\Ecommerce\Framework\Services\Product\ProductCombinationFacade as ProductCombinationService;
use Hideyo\Ecommerce\Framework\Services\Coupon\CouponFacade as CouponService;

class Cart
{
    /**
     * the item storage
     *
     * @var
     */
    protected $session;
    /**
     * the event dispatcher
     *
     * @var
     */
    protected $events;
    /**
     * the cart session key
     *
     * @var
     */
    protected $instanceName;

    /**    
     *
     * @var
     */
    protected $sessionKeyCartItems;

    /**
     * the session key use to persist cart conditions
     *
     * @var
     */
    protected $sessionKeyCartConditions;

    /**
     * the session key use to persist voucher
     *
     * @var
     */
    protected $sessionKeyCartVoucher;

    /**
     * Configuration to pass to ItemCollection
     *
     * @var
     */
    protected $config;

    /**
     * our object constructor
     *
     * @param $session
     * @param $events
     * @param $instanceName
     */
    public function __construct($session, $events, $instanceName, $session_key, $config)
    {
        $this->events = $events;
        $this->session = $session;
        $this->instanceName = $instanceName;
        $this->sessionKeyCartItems = $session_key . '_cart_items';
        $this->sessionKeyCartConditions = $session_key . '_cart_conditions';
        $this->sessionKeyCartVoucher = $session_key . '_voucher';
        $this->fireEvent('created');
        $this->config = $config;
    }


    /**
     * get instance name of the cart
     *
     * @return string
     */
    public function getInstanceName()
    {
        return $this->instanceName;
    }

    /**
     * get an item on a cart by item ID
     *
     * @param $itemId
     * @return mixed
     */
    public function get($itemId)
    {
        return $this->getContent()->get($itemId);
    }

    /**
     * check if an item exists by item ID
     *
     * @param $itemId
     * @return bool
     */
    public function has($itemId)
    {
        return $this->getContent()->has($itemId);
    }

    /**
     * add item to the cart, it can be an array or multi dimensional array
     *
     * @param string|array $id
     * @param string $name
     * @param float $price
     * @param int $quantity
     * @param array $attributes
     * @param CartCondition|array $conditions
     * @return $this
     * @throws InvalidItemException
     */
    public function add($id, $attributes = array(), $quantity = null, $conditions = array(), $orderId = 0)
    {
        // validate data
        $item = array(
            'id' => $id,
            'orderId' => $orderId,
            'attributes' => $attributes,
            'quantity' => $quantity,
            'conditions' => $conditions
        );

        $cart = $this->getContent();
        // if the item is already in the cart we will just update it
        if ($cart->has($id)) {
            $this->update($id, $item);
        } else {
            $this->addRow($id, $item);
        }
        return $this;
    }

    /**
     * update a cart
     *
     * @param $id
     * @param $data
     *
     * the $data will be an associative array, you don't need to pass all the data, only the key value
     * of the item you want to update on it
     * @return bool
     */
    public function update($id, $data)
    {
        if($this->fireEvent('updating', $data) === false) {
            return false;
        }
        $cart = $this->getContent();
        $item = $cart->pull($id);
        foreach ($data as $key => $value) {
            // if the key is currently "quantity" we will need to check if an arithmetic
            // symbol is present so we can decide if the update of quantity is being added
            // or being reduced.
            if ($key == 'quantity') {
                // we will check if quantity value provided is array,
                // if it is, we will need to check if a key "relative" is set
                // and we will evaluate its value if true or false,
                // this tells us how to treat the quantity value if it should be updated
                // relatively to its current quantity value or just totally replace the value
                if (is_array($value)) {
                    if (isset($value['relative'])) {
                        if ((bool)$value['relative']) {
                            $item = $this->updateQuantityRelative($item, $key, $value['value']);
                        } else {
                            $item = $this->updateQuantityNotRelative($item, $key, $value['value']);
                        }
                    }
                } else {
                    $item = $this->updateQuantityRelative($item, $key, $value);
                }
            } elseif ($key == 'attributes') {
                $item[$key] = new ItemAttributeCollection($value);
            } else {
                $item[$key] = $value;
            }
        }
        $cart->put($id, $item);
        $this->save($cart);
        $this->fireEvent('updated', $item);
        return true;
    }

    /**
     * update a cart item quantity relative to its current quantity
     *
     * @param $item
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function updateQuantityRelative($item, $key, $value)
    {
        if (preg_match('/\-/', $value) == 1) {
            $value = (int)str_replace('-', '', $value);
            // we will not allowed to reduced quantity to 0, so if the given value
            // would result to item quantity of 0, we will not do it.
            if (($item[$key] - $value) > 0) {
                $item[$key] -= $value;
            }
        } elseif (preg_match('/\+/', $value) == 1) {
            $item[$key] += (int)str_replace('+', '', $value);
        } else {
            $item[$key] += (int)$value;
        }
        return $item;
    }

    /**
     * update cart item quantity not relative to its current quantity value
     *
     * @param $item
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function updateQuantityNotRelative($item, $key, $value)
    {
        $item[$key] = (int)$value;
        return $item;
    }

    /**
     * get the cart
     *
     * @return CartCollection
     */
    public function getContent()
    {
        return (new CartCollection($this->session->get($this->sessionKeyCartItems)));
    }

    /**
     * add row to cart collection
     *
     * @param $id
     * @param $item
     * @return bool
     */
    protected function addRow($id, $item)
    {
        if($this->fireEvent('adding', $item) === false) {
            return false;
        }
        $cart = $this->getContent();
        $cart->put($id, new ItemCollection($item, $this->config));
        $this->save($cart);
        $this->fireEvent('added', $item);
        return true;
    }

    /**
     * add a condition on the cart
     *
     * @param CartCondition|array $condition
     * @return $this
     * @throws InvalidConditionException
     */
    public function condition($condition)
    {
        if (is_array($condition)) {
            foreach ($condition as $c) {
                $this->condition($c);
            }
            return $this;
        }
        if (!$condition instanceof CartCondition) throw new InvalidConditionException('Argument 1 must be an instance of \'Darryldecode\Cart\CartCondition\'');
        $conditions = $this->getConditions();
        // Check if order has been applied
        if ($condition->getOrder() == 0) {
            $last = $conditions->last();
            $condition->setOrder(!is_null($last) ? $last->getOrder() + 1 : 1);
        }
        $conditions->put($condition->getName(), $condition);
        $conditions = $conditions->sortBy(function ($condition, $key) {
            return $condition->getOrder();
        });
        $this->saveConditions($conditions);
        return $this;
    }

    /**
     * get conditions applied on the cart
     *
     * @return CartConditionCollection
     */
    public function getConditions()
    {
        return new CartConditionCollection($this->session->get($this->sessionKeyCartConditions));
    }

    /**
     * get condition applied on the cart by its name
     *
     * @param $conditionName
     * @return CartCondition
     */
    public function getCondition($conditionName)
    {
        return $this->getConditions()->get($conditionName);
    }

    /**
     * Get all the condition filtered by Type
     * Please Note that this will only return condition added on cart bases, not those conditions added
     * specifically on an per item bases
     *
     * @param $type
     * @return CartConditionCollection
     */
    public function getConditionsByType($type)
    {
        return $this->getConditions()->filter(function (CartCondition $condition) use ($type) {
            return $condition->getType() == $type;
        });
    }

    /**
     * Remove all the condition with the $type specified
     * Please Note that this will only remove condition added on cart bases, not those conditions added
     * specifically on an per item bases
     *
     * @param $type
     * @return $this
     */
    public function removeConditionsByType($type)
    {
        $this->getConditionsByType($type)->each(function ($condition) {
            $this->removeCartCondition($condition->getName());
        });
    }

    /**
     * removes a condition on a cart by condition name,
     * this can only remove conditions that are added on cart bases not conditions that are added on an item/product.
     * If you wish to remove a condition that has been added for a specific item/product, you may
     * use the removeItemCondition(itemId, conditionName) method instead.
     *
     * @param $conditionName
     * @return void
     */
    public function removeCartCondition($conditionName)
    {
        $conditions = $this->getConditions();
        $conditions->pull($conditionName);
        $this->saveConditions($conditions);
    }

    /**
     * remove a condition that has been applied on an item that is already on the cart
     *
     * @param $itemId
     * @param $conditionName
     * @return bool
     */
    public function removeItemCondition($itemId, $conditionName)
    {
        if (!$item = $this->getContent()->get($itemId)) {
            return false;
        }
        if ($this->itemHasConditions($item)) {
            // NOTE:
            // we do it this way, we get first conditions and store
            // it in a temp variable $originalConditions, then we will modify the array there
            // and after modification we will store it again on $item['conditions']
            // This is because of ArrayAccess implementation
            // see link for more info: http://stackoverflow.com/questions/20053269/indirect-modification-of-overloaded-element-of-splfixedarray-has-no-effect
            $tempConditionsHolder = $item['conditions'];
            // if the item's conditions is in array format
            // we will iterate through all of it and check if the name matches
            // to the given name the user wants to remove, if so, remove it
            if (is_array($tempConditionsHolder)) {
                foreach ($tempConditionsHolder as $k => $condition) {
                    if ($condition->getName() == $conditionName) {
                        unset($tempConditionsHolder[$k]);
                    }
                }
                $item['conditions'] = $tempConditionsHolder;
            }
            // if the item condition is not an array, we will check if it is
            // an instance of a Condition, if so, we will check if the name matches
            // on the given condition name the user wants to remove, if so,
            // lets just make $item['conditions'] an empty array as there's just 1 condition on it anyway
            else {
                $conditionInstance = "Darryldecode\\Cart\\CartCondition";
                if ($item['conditions'] instanceof $conditionInstance) {
                    if ($tempConditionsHolder->getName() == $conditionName) {
                        $item['conditions'] = array();
                    }
                }
            }
        }
        $this->update($itemId, array(
            'conditions' => $item['conditions']
        ));
        return true;
    }

    /**
     * remove all conditions that has been applied on an item that is already on the cart
     *
     * @param $itemId
     * @return bool
     */
    public function clearItemConditions($itemId)
    {
        if (!$item = $this->getContent()->get($itemId)) {
            return false;
        }
        $this->update($itemId, array(
            'conditions' => array()
        ));
        return true;
    }

    /**
     * clears all conditions on a cart,
     * this does not remove conditions that has been added specifically to an item/product.
     * If you wish to remove a specific condition to a product, you may use the method: removeItemCondition($itemId, $conditionName)
     *
     * @return void
     */
    public function clearCartConditions()
    {
        $this->session->put(
            $this->sessionKeyCartConditions,
            array()
        );
    }

    /**
     * get cart sub total without conditions
     * @param bool $formatted
     * @return float
     */
    public function getSubTotalWithoutConditions($formatted = true)
    {
        $cart = $this->getContent();
        $sum = $cart->sum(function ($item) {
            return $item->getOriginalPriceWithTaxSum();
        });

        return Helpers::formatValue(floatval($sum), $formatted, $this->config);
    }    

    /**
     * get cart sub total with tax
     * @param bool $formatted
     * @return float
     */
    public function getSubTotalWithTax($formatted = true)
    {
        $cart = $this->getContent();
        $sum = $cart->sum(function ($item) {
            return $item->getOriginalPriceWithTaxSum(false);
        });


        return Helpers::formatValue(floatval($sum), $formatted, $this->config);
    }

    /**
     * get cart sub total with out tax
     * @param bool $formatted
     * @return float
     */
    public function getSubTotalWithoutTax($formatted = true)
    {
        $cart = $this->getContent();
        $sum = $cart->sum(function ($item) {
            return $item->getOriginalPriceWithoutTaxSum(false);
        });

        return Helpers::formatValue(floatval($sum), $formatted, $this->config);
    }

    /**
     * the new total with tax in which conditions are already applied
     *
     * @return float
     */
    public function getTotalWithTax($formatted = true)
    {

        $subTotal = $this->getSubTotalWithTax(false);
        $newTotal = 0.00;
        $process = 0;
        $conditions = $this
            ->getConditions()
            ->filter(function ($cond) {
                return $cond->getTarget() === 'subtotal';
            });
        // if no conditions were added, just return the sub total
        if (!$conditions->count()) {
            return Helpers::formatValue(floatval($subTotal), $formatted, $this->config);
        }
        $conditions
            ->each(function ($cond) use ($subTotal, &$newTotal, &$process) {
                $toBeCalculated = ($process > 0) ? $newTotal : $subTotal;
                $newTotal = $cond->applyCondition($toBeCalculated);
                $process++;
            });


        return Helpers::formatValue(floatval($newTotal), $formatted, $this->config);
    }


    /**
     * the new total without tax in which conditions are already applied
     *
     * @return float
     */
    public function getTotalWithoutTax($formatted = true)
    {
        $subTotal = $this->getSubTotalWithoutTax(false);
        $newTotal = 0.00;
        $process = 0;
        $conditions = $this
            ->getConditions()
            ->filter(function ($cond) {
                return $cond->getTarget() === 'subtotal';
            });
        // if no conditions were added, just return the sub total
        if (!$conditions->count()) {
            return $subTotal;
        }
        $conditions
            ->each(function ($cond) use ($subTotal, &$newTotal, &$process) {
                $toBeCalculated = ($process > 0) ? $newTotal : $subTotal;
                $newTotal = $cond->applyConditionWithoutTax($toBeCalculated);
                $process++;
            });

        return Helpers::formatValue(floatval($newTotal), $formatted, $this->config);
    }

    /**
     * removes an item on cart by item ID
     *
     * @param $id
     * @return bool
     */
    public function remove($id)
    {
        $cart = $this->getContent();
        if($this->fireEvent('removing', $id) === false) {
            return false;
        }
        $cart->forget($id);
        $this->save($cart);
        $this->fireEvent('removed', $id);
        return true;
    }

    /**
     * save the cart
     *
     * @param $cart CartCollection
     */
    protected function save($cart)
    {
        $this->session->put($this->sessionKeyCartItems, $cart);
    }

    /**
     * save the cart conditions
     *
     * @param $conditions
     */
    protected function saveConditions($conditions)
    {
        $this->session->put($this->sessionKeyCartConditions, $conditions);
    }

    /**
     * save the cart voucher
     *
     * @param $voucher
     */
    public function saveVoucher($voucher)
    {
        $this->session->put($this->sessionKeyCartVoucher, $voucher);
    }

    /**
     * get the cart voucher
     *
     */
    public function getVoucher()
    {
        $voucher = $this->session->get($this->sessionKeyCartVoucher);
        if($voucher){

        $totalWithTax = self::getTotalWithTax();
        $totalWithoutTax = self::getTotalWithoutTax();
        $voucher['used_value_with_tax']  = $voucher['value'];
        $voucher['used_value_without_tax']  = $voucher['value'];
        if($totalWithTax <= $voucher['value']) {
            $voucher['used_value_with_tax']  = $voucher['value'] - ($voucher['value'] - $totalWithTax);
        }

        if($totalWithTax <= $voucher['value']) {
            $voucher['used_value_without_tax']  = $voucher['value'] - ($voucher['value'] - $totalWithoutTax);
        }

        $this->session->put($this->sessionKeyCartVoucher, $voucher);

        }

        return $this->session->get($this->sessionKeyCartVoucher);
    }

    public function getToPayWithTax($formatted = true) 
    {
        $voucher = self::getVoucher();
        $toPay = self::getTotalWithTax(false) - $voucher['used_value_with_tax'];

        return Helpers::formatValue(floatval($toPay), $formatted, $this->config);      
    }

    public function getToPayWithoutTax($formatted = true) 
    {
        $voucher = self::getVoucher();
        $toPay = self::getTotalWithoutTax(false) - $voucher['used_value_without_tax'];

        return Helpers::formatValue(floatval($toPay), $formatted, $this->config); 
    }

    /**
     * clear the cart voucher
     *
     */
    public function clearVoucher()
    {
        $this->session->put($this->sessionKeyCartVoucher, array());
    }

    /**
     * clear cart
     * @return bool
     */
    public function clear()
    {
        if($this->fireEvent('clearing') === false) {
            return false;
        }
        $this->session->put(
            $this->sessionKeyCartItems,
            array()
        );
        $this->fireEvent('cleared');
        return true;
    }

    /**
     * @param $name
     * @param $value
     * @return mixed
     */
    protected function fireEvent($name, $value = [])
    {
        return $this->events->dispatch($this->getInstanceName() . '.' . $name, array_values([$value, $this]));
    }  

    public function updateAmountProduct($productId, $amount, $leadingAttributeId, $productAttributeId)
    {
        $explode = explode('-', $productId);
        $product = ProductService::selectOneById($explode[0]);

        $productCombination = false;
        if (isset($explode[1])) {
            $productAttributeId = $explode[1];
            $productCombination = ProductCombinationService::getModel()->where('id', '=', $productAttributeId)->first();
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

                $productArray['product_images'] =     ProductService::ajaxProductImages($product, array($leadingAttributeId), $productAttributeId);
            }

            if ($product->amountSeries()->where('active', '=', '1')->count()) {
                $productArray['product_amount_series'] = true;
                $productArray['product_amount_series_range'] = $product->amountSeries()->where('active', '=', '1')->first()->range();
            }

            if($productArray['price_details']['amount'] > 0) {

                if($amount >= $productArray['price_details']['amount']) {
                    $amount = $productArray['price_details']['amount'];
                }

                $this->update($productId, array(
                  'quantity' => array(
                      'relative' => false,
                      'value' => $amount
                  ),
                ));
            } else {
                $this->remove($productId);
            }

            if($this->getConditionsByType('sending_method_country_price')->count()) {
                $this->updateSendingMethodCountryPrice($this->getConditionsByType('sending_method_country_price')->first()->getAttributes()['data']['sending_method_country_price_id']);  
            }
        }
    }
   
    public function postProduct($productId, $productCombinationId = false, $leadingAttributeId, $productAttributeId, $amount)
    {
        $product = ProductService::selectOneByShopIdAndId(config()->get('app.shop_id'), $productId);
        $productCombination = false;

        if ($productCombinationId) {
            $productCombination = ProductCombinationService::getModel()->where('id', '=', $productCombinationId)->first();
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

                $productArray['product_images'] =     ProductService::ajaxProductImages($product, array($leadingAttributeId, $productAttributeId));
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

            return $this->add($productId, $productArray,  $amount, $discountCondition);
        }

        return false;
    }

    public function updateSendingMethod($sendingMethodId)
    {
        $sendingMethod = SendingmethodService::selectOneByShopIdAndId(config()->get('app.shop_id'), $sendingMethodId);
        $sendingMethodArray = array();
        if (isset($sendingMethod->id)) {
            $sendingMethodArray = $sendingMethod->toArray();          
            $sendingMethodArray['price_details'] = $sendingMethod->getPriceDetails();
            if($sendingMethod->relatedPaymentMethodsActive) {
                $sendingMethodArray['related_payment_methods_list'] = $sendingMethod->relatedPaymentMethodsActive->pluck('title', 'id');                
            }

        }

        $this->removeConditionsByType('sending_method');
        $condition = new \Hideyo\Ecommerce\Framework\Services\Cart\CartCondition(array(
            'name' => 'Sending method',
            'type' => 'sending_method',
            'target' => 'subtotal',
            'value' => 0,
            'attributes' => array(
                'data' => $sendingMethodArray
            )
        ));

        $this->condition($condition);

        if (!$this->getConditionsByType('payment_method')->count() and $sendingMethod->relatedPaymentMethodsActive) {
            $this->updatePaymentMethod($sendingMethod->relatedPaymentMethodsActive->first()->id);
        }

        return true;
    }

    public function updatePaymentMethod($paymentMethodId)
    {
        $paymentMethod = PaymentMethodService::selectOneByShopIdAndId(config()->get('app.shop_id'), $paymentMethodId);

        $paymentMethodArray = array();
        if (isset($paymentMethod->id)) {
            $paymentMethodArray = $paymentMethod->toArray();
            $paymentMethodArray['price_details'] = $paymentMethod->getPriceDetails();
        }

        $valueExTax = $paymentMethodArray['price_details']['original_price_ex_tax'];
        $valueIncTax = $paymentMethodArray['price_details']['original_price_inc_tax'];
        $shop = ShopService::find(config()->get('app.shop_id'));
        $value = $valueIncTax;
        $freeSending = ( $paymentMethodArray['no_price_from'] - $this->getSubTotalWithTax());


        if ($freeSending < 0) {
            $value = 0;
            $valueIncTax = 0;
            $valueExTax = 0;
        }

        $paymentMethodArray['value_inc_tax'] = $valueIncTax;
        $paymentMethodArray['value_ex_tax'] = $valueExTax;

        $this->removeConditionsByType('payment_method');
        $condition = new \Hideyo\Ecommerce\Framework\Services\Cart\CartCondition(array(
            'name' => 'Payment method',
            'type' => 'payment_method',
            'target' => 'subtotal',
            'value' => $value,
            'attributes' => array(
                'data' => $paymentMethodArray
            )
        ));

        return $this->condition($condition);
    }

    public function updateSendingMethodCountryPrice($sendingMethodCountryPriceId)
    {
        $sendingMethodCountryPrice = SendingmethodService::selectOneCountryPriceByShopIdAndId(config()->get('app.shop_id'), $sendingMethodCountryPriceId);
     
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
            $freeSending = ( $sendingMethodArray['no_price_from'] - $this->getSubTotalWithTax());
      
            if ($freeSending < 0) {
                $value = 0;
                $valueIncTax = 0;
                $valueExTax = 0;
            }

            $sendingMethodArray['value_inc_tax'] = $valueIncTax;
            $sendingMethodArray['value_ex_tax'] = $valueExTax;

            $this->removeConditionsByType('sending_method_country_price');
            $condition1 = new \Hideyo\Ecommerce\Framework\Services\Cart\CartCondition(array(
                'name' => 'Sending method country price',
                'type' => 'sending_method_country_price',
                'target' => 'subtotal',
                'value' => 0,
                'attributes' => array(
                    'data' => $sendingMethodArray
                )
            ));

            $this->removeConditionsByType('sending_cost');
            $condition2 = new \Hideyo\Ecommerce\Framework\Services\Cart\CartCondition(array(
                'name' => 'Sending Cost',
                'type' => 'sending_cost',
                'target' => 'subtotal',
                'value' => $value,
                'attributes' => array(
                    'data' => $sendingMethodArray
                )
            ));

            $this->condition([$condition1, $condition2]);

            if (!$this->getConditionsByType('payment_method')->count() and $sendingMethod->relatedPaymentMethodsActive->first()->id) {
                $this->updatePaymentMethod($sendingMethod->relatedPaymentMethodsActive->first()->id);
            }

            return true;
        }     
    }

    public function updateOrderStatus($orderStatusId) 
    {
        session()->put('orderStatusId', $orderStatusId);
    }

    public function addClient($clientId) 
    {
        session()->put('orderClientId', $clientId);
        session()->forget('orderClientBillAddressId');
        session()->forget('orderClientDeliveryAddressId');
    }

    public function addClientBillAddress($clientBillAddressId) 
    {
        session()->put('orderClientBillAddressId', $clientBillAddressId);
    }

    public function addClientDeliveryAddress($clientDeliveryAddressId) 
    {
        session()->put('orderClientDeliveryAddressId', $clientDeliveryAddressId);
    }

    public function updateCouponCode($couponCode) 
    {
        $this->removeConditionsByType('coupon');
        $coupon = CouponService::selectOneByShopIdAndCode(config()->get('app.shop_id'), $couponCode);
        
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

                $this->setCouponCode($discountValue, $couponData, $couponCode);
            }

            if($coupon->type == 'product') {

                if($coupon->products()->count()) {
                    
                    foreach ($this->getContent() as $row) {

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

                    $this->setCouponCode($discountValue, $couponData, $couponCode);
                }
            }

            if($coupon->type == 'product_category') {

                if($coupon->productCategories()->count()) {

                    foreach ($this->getContent()->sortBy('id')  as $row) {

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

                    $this->setCouponCode($discountValue, $couponData, $couponCode);
                }
            }

            if($coupon->type == 'sending_method') {

                if($coupon->sendingMethodCountries()->count()) {

                    foreach ($coupon->sendingMethodCountries as $country) {

                        if($this->getConditionsByType('sending_method_country_price')){
                            if($country->name == $this->getConditionsByType('sending_method_country_price')->first()->getAttributes()['data']['sending_method_country_price']['name']) {

                                if($coupon->discount_way == 'total') {
                                    $discountValue += $coupon->value;
                                } elseif ($coupon->discount_way == 'percent') {
                                    $value = $coupon->value / 100; 
                                    $discountValue += $this->getConditionsByType('sending_cost')->first()->getValue() * $value;
                                } 
                            }
                        }
                    }

                    $this->setCouponCode($discountValue, $couponData, $couponCode);

                } elseif($coupon->sendingMethods()->count()) {

                    foreach ($coupon->sendingMethods as $sendingMethod) {

                        if($this->getConditionsByType('sending_cost')){

                            if($sendingMethod->id == $this->getConditionsByType('sending_cost')->first()->getAttributes()['data']['sending_method']['id']) {

                                if($coupon->discount_way == 'total') {
                                    $discountValue += $coupon->value;
                                } elseif ($coupon->discount_way == 'percent') {
                                    $value = $coupon->value / 100; 
                                    $discountValue += $this->getConditionsByType('sending_cost')->first()->getValue() * $value;
                                }                 
                            }
                        }            
                    }

                    $this->setCouponCode($discountValue, $couponData, $couponCode);
                }
            }

            if($coupon->type == 'payment_method') {

                if($coupon->paymentMethods()->count()) {

                    foreach ($coupon->paymentMethods as $paymentMethod) {

                        if($this->getConditionsByType('payment_method')){

                            if($paymentMethod->id == $this->getConditionsByType('payment_method')->first()->getAttributes()['data']['id']) {

                                if($coupon->discount_way == 'total') {
                                    $discountValue += $coupon->value;
                                } elseif ($coupon->discount_way == 'percent') {
                                    $value = $coupon->value / 100; 
                                    $discountValue += $this->getConditionsByType('payment_method')->first()->getValue() * $value;
                                }                 
                            }
                        }            
                    }

                    $this->setCouponCode($discountValue, $couponData, $couponCode);
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

        $this->condition($condition);
    }

    public function replaceTags($content, $order)
    {
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