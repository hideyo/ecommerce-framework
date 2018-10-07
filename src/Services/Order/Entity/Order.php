<?php 

namespace Hideyo\Ecommerce\Framework\Services\Order\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;
use Hideyo\Ecommerce\Framework\Services\Shop\Entity\Shop;
use Hideyo\Ecommerce\Framework\Services\PaymentMethod\Entity\PaymentMethod;
use Hideyo\Ecommerce\Framework\Services\SendingMethod\Entity\SendingMethod;
use Carbon\Carbon;

class Order extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'order';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['id', 'validated', 'client_id', 'shop_id', 'price_with_tax', 'price_without_tax', 'total_discount', 'coupon_id', 'coupon_title',  'coupon_discount_way', 'coupon_type', 'coupon_value', 'coupon_code', 'coupon_group_title', 'bill_order_address_id', 'delivery_order_address_id', 'order_status_id', 'mollie_payment_id', 'comments', 'present_gender', 'present_occassion', 'present_message', 'browser_detect'];

    public static function boot()
    {
        static::creating(function ($model) {

            if ($model->created_at) {
                $order = Order::where('shop_id', '=', $model->shop_id)->where('created_at', '>=', Carbon::createFromFormat('Y-m-d H:i:s', $model->created_at)->year)->orderBy('id', 'desc')->first();
                $shop = Shop::find($model->shop_id);
                $prefix = strtoupper(substr($shop->slug, 0, 4));

                if ($order) {
                    $model->generated_year_order_id = $order->generated_year_order_id + 1;
                    $model->generated_custom_order_id = $order->generated_year_order_id + 1;
                    $model->generated_custom_order_id = $prefix.Carbon::createFromFormat('Y-m-d H:i:s', $model->created_at)->format('y').$model->generated_custom_order_id;
                } else {
                    $model->generated_year_order_id = 1;
                    $model->generated_custom_order_id = $prefix.Carbon::createFromFormat('Y-m-d H:i:s', $model->created_at)->format('y').'1';
                }
            } else {
                $order = Order::where('shop_id', '=', $model->shop_id)->where('created_at', '>=', Carbon::now()->year)->orderBy('id', 'desc')->first();
                $shop = Shop::find($model->shop_id);
                $prefix = strtoupper(substr($shop->slug, 0, 4));

                if ($order) {
                    $model->generated_year_order_id = $order->generated_year_order_id + 1;
                    $model->generated_custom_order_id = $order->generated_year_order_id + 1;
                    $model->generated_custom_order_id = $prefix.Carbon::now()->format('y').$model->generated_custom_order_id;
                } else {
                    $model->generated_year_order_id = 1;
                    $model->generated_custom_order_id = $prefix.Carbon::now()->format('y').'1';
                }
            }
        });

          parent::boot();
    }

    public function products()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderProduct');
    }

    public function client()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Client\Entity\Client');
    }

    public function orderStatus()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderStatus');
    }

    public function orderPaymentLog()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderPaymentLog');
    }

    public function orderAddress()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderAddress');
    }

    public function shop()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Shop\Entity\Shop');
    }


    public function coupon()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\Coupon');
    }

    public function invoice()
    {
        return $this->hasOne('Hideyo\Ecommerce\Framework\Services\Invoice\Entity\Invoice');
    }

    public function orderDeliveryAddress()
    {
        return $this->hasOne('Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderAddress', 'id', 'delivery_order_address_id');
    }

    public function orderBillAddress()
    {
        return $this->hasOne('Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderAddress', 'id', 'bill_order_address_id');
    }

    public function orderSendingMethod()
    {
        return $this->hasOne('Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderSendingMethod');
    }

    public function orderPaymentMethod()
    {
        return $this->hasOne('Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderPaymentMethod');
    }

    public function taxTotal()
    {
        return number_format($this->price_with_tax - $this->price_without_tax, 2, '.', '');
    }

    public function getTotalDiscountNumberFormat()
    {
        return number_format($this->total_discount, 2, '.', '');
    }

    public function getPriceWithTaxNumberFormat()
    {
        return number_format($this->price_with_tax, 2, '.', '');
    }

    public function getPriceWithTaxNoFormat()
    {
        return $this->price_with_tax;
    }

    public function getBrowserDetectArray()
    {
        return unserialize($this->browser_detect);
    }

    public function getPriceWithoutTaxNumberFormat()
    {
        return number_format($this->price_without_tax, 2, '.', '');
    }

    public function taxDetails()
    {
        $taxArray = array();

        if ($this->products) {
            foreach ($this->products as $product) {
                if (!isset($taxArray[$product->tax_rate])) {
                    $taxArray[$product->tax_rate] = 0;
                }

                $taxArray[$product->tax_rate] += $product->total_price_with_tax - $product->total_price_without_tax;
            }
        }

        if ($this->orderSendingMethod) {
            if (!isset($taxArray[$this->orderSendingMethod->tax_rate])) {
                $taxArray[$this->orderSendingMethod->tax_rate] = 0;
            }
            
            $taxArray[$this->orderSendingMethod->tax_rate] += $this->orderSendingMethod->price_with_tax - $this->orderSendingMethod->price_without_tax;
        }


        if ($this->orderPaymentMethod) {
            if (!isset($taxArray[$this->orderPaymentMethod->tax_rate])) {
                $taxArray[$this->orderPaymentMethod->tax_rate] = 0;
            }
            
            $taxArray[$this->orderPaymentMethod->tax_rate] += $this->orderPaymentMethod->price_with_tax - $this->orderPaymentMethod->price_without_tax;
        }

        return $taxArray;
    }
}
