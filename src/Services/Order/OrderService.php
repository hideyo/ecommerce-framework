<?php

namespace Hideyo\Ecommerce\Framework\Services\Order;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
use Hideyo\Ecommerce\Framework\Services\Client\ClientFacade as ClientService;
use Hideyo\Ecommerce\Framework\Services\SendingMethod\SendingMethodFacade as SendingMethodService;
use Hideyo\Ecommerce\Framework\Services\PaymentMethod\PaymentMethodFacade as PaymentMethodService;
use Cart; 

class OrderService extends BaseService
{
	public function __construct(OrderRepository $order)
	{
		$this->repo = $order;
	} 

    public function createAddress(array $attributes, $orderId)
    {
        if (auth('hideyobackend')->check()) {
            $userId = auth('hideyobackend')->user()->id;
            $attributes['modified_by_user_id'] = $userId;
        }

        $attributes['order_id'] = $orderId;  
        $this->getAddressModel()->fill($attributes);
        $this->getAddressModel()->save();
        
        return $this->getAddressModel();
    }

    public function create(array $attributes)
    {
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        $this->repo->getModel()->fill($attributes);
        $this->repo->getModel()->save();

        if (isset($attributes['categories'])) {
            $this->repo->getModel()->categories()->sync($attributes['categories']);
        }
        
        return $this->model;
    }

    public function updateById(array $attributes, $id)
    {
        $model = $this->find($id);
        if (auth('hideyobackend')->check()) {
            $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
            $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        }

        $model->fill($attributes);
        return $model->save();   
    }

    public function addProducts($orderModel, $products) 
    {
        foreach ($products  as $product) {

            $quantity = $product->quantity;
            $newProduct = array(
                'product_id' => $product['attributes']['id'],
                'title' => $product['attributes']['title'],
                'original_price_without_tax' => $product['attributes']['price_details']['original_price_ex_tax'],
                'original_price_with_tax' => $product['attributes']['price_details']['original_price_inc_tax'],
                'original_total_price_without_tax' => $quantity * $product['attributes']['price_details']['original_price_ex_tax'],
                'original_total_price_with_tax' => $quantity * $product['attributes']['price_details']['original_price_inc_tax'],
                'price_without_tax' => $product->getOriginalPriceWithoutTaxAndConditions(false),
                'price_with_tax' => $product->getOriginalPriceWithTaxAndConditions(false),
                'total_price_without_tax' => $product->getOriginalPriceWithoutTaxSum(false),
                'total_price_with_tax' => $product->getOriginalPriceWithTaxSum(false),
                'amount' => $quantity,
                'tax_rate' => $product['attributes']['tax_rate'],
                'tax_rate_id' => $product['attributes']['tax_rate_id'],
                'weight' => $product['attributes']['weight'],
                'reference_code' => $product['attributes']['reference_code'],
            );

            if (isset($product['attributes']['product_combination_id'])) {
                $newProduct['product_attribute_id'] = $product['attributes']['product_combination_id'];
                $productCombinationTitleArray = array();

                if (isset($product['attributes']['product_combination_title'])) {
                    $productCombinationTitle = array();

                    foreach ($product['attributes']['product_combination_title'] as $key => $val) {

                        $productCombinationTitle[] = $key.': '.$val;
                    }

                    $newProduct['product_attribute_title'] = implode(', ', $productCombinationTitle);
                
                }
            }
            $modelProduct = $this->getProductModel();
            $newProducts[] = new $modelProduct($newProduct);
        }

        $orderModel->products()->saveMany($newProducts);

        return $orderModel;
    }

    public function createOrderFrontend(array $attributes, $shopId, $noAccountUser)
    {
        $attributes['shop_id'] = $shopId;
        $attributes['client_id'] = $attributes['user_id'];
        $client  = ClientService::selectOneByShopIdAndId($shopId, $attributes['user_id']);

        $this->repo->getModel()->fill($attributes);
        $this->repo->getModel()->save();

        if (!Cart::getContent()->count()) {
            return false;
        }

        $this->addProducts($this->repo->getModel(), Cart::getContent());
        
        if ($client) {
            if ($client->clientDeliveryAddress) {
                $deliveryOrderAddress = $this->createAddress($client->clientDeliveryAddress->toArray(), $this->repo->getModel()->id);
            }

            if ($client->clientBillAddress) {
                $billOrderAddress = $this->createAddress($client->clientBillAddress->toArray(), $this->repo->getModel()->id);
            }

            $this->repo->getModel()->fill(array('delivery_order_address_id' => $deliveryOrderAddress->id, 'bill_order_address_id' => $billOrderAddress->id));
            $this->repo->getModel()->save();

        } elseif ($noAccountUser) {
            if (isset($noAccountUser['delivery'])) {
                $deliveryOrderAddress = $billOrderAddress = $this->createAddress($noAccountUser['delivery'], $this->repo->getModel()->id);
            } else {
                $deliveryOrderAddress = $billOrderAddress = $this->createAddress($noAccountUser, $this->repo->getModel()->id);
            }

            $billOrderAddress = $billOrderAddress = $this->createAddress($noAccountUser, $this->repo->getModel()->id);
            $this->repo->getModel()->fill(array('delivery_order_address_id' => $deliveryOrderAddress->id, 'bill_order_address_id' => $billOrderAddress->id));
            $this->repo->getModel()->save();
        }

        if (Cart::getConditionsByType('sending_method')->count()) {
            $attributes = Cart::getConditionsByType('sending_method')->first()->getAttributes();
            $sendingMethod = SendingMethodService::find($attributes['data']['id']);
            $price = $sendingMethod->getPriceDetails();
            $sendingMethodArray = $sendingMethod->toArray();
            $sendingMethodArray['price_with_tax'] = Cart::getConditionsByType('sending_method')->first()->getAttributes()['data']['price_details']['original_price_inc_tax'];
            $sendingMethodArray['price_without_tax'] = Cart::getConditionsByType('sending_method')->first()->getAttributes()['data']['price_details']['original_price_ex_tax'];
            $sendingMethodArray['tax_rate'] = $price['tax_rate'];
            $sendingMethodArray['sending_method_id'] = $sendingMethod->id;
            $sendingMethodArray['order_id'] = $this->repo->getModel()->id;
            $orderSendingMethod = $this->getSendingMethodModel()->fill($sendingMethodArray);
            $orderSendingMethod = $orderSendingMethod->save();
        }

        if (Cart::getConditionsByType('payment_method')->count()) {
            $attributes = Cart::getConditionsByType('payment_method')->first()->getAttributes();
            $paymentMethod = PaymentMethodService::find($attributes['data']['id']);
            $price = $paymentMethod->getPriceDetails();
            $paymentMethodArray = $paymentMethod->toArray();
            $paymentMethodArray['price_with_tax'] = Cart::getConditionsByType('payment_method')->first()->getAttributes()['data']['value_inc_tax'];
            $paymentMethodArray['price_without_tax'] = Cart::getConditionsByType('payment_method')->first()->getAttributes()['data']['value_ex_tax'];
            $paymentMethodArray['tax_rate'] = $price['tax_rate'];
            $paymentMethodArray['payment_method_id'] = $paymentMethod->id;
            $paymentMethodArray['order_id'] = $this->repo->getModel()->id;
            $orderPaymentMethod = $this->getPaymentMethodModel()->fill($paymentMethodArray);
            $orderPaymentMethod = $orderPaymentMethod->save();
        }

        if ($this->repo->getModel()->orderPaymentMethod->paymentMethod->order_confirmed_order_status_id) {
            $this->repo->getModel()->fill(array('order_status_id' => $this->repo->getModel()->orderPaymentMethod->paymentMethod->order_confirmed_order_status_id));
            $this->repo->getModel()->save();
        }

        return $this->repo->getModel();
    }

    public function getProductModel()
    {
        return $this->repo->getProductModel();
    }

    public function getAddressModel()
    {
        return $this->repo->getAddressModel();
    }

    public function getSendingMethodModel()
    {
        return $this->repo->getOrderSendingMethodModel();
    }

    public function getPaymentMethodModel()
    {
        return $this->repo->getOrderPaymentMethodModel();
    }

    public function updateStatus($id, $orderStatusId)
    {
        $model = $this->find($id);

        $attributes['order_status_id'] = $orderStatusId;
        if (count($attributes) > 0) {
            $model->fill($attributes);
            $model->save();
        }

        return $model;
    }
}