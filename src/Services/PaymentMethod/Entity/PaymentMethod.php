<?php 

namespace Hideyo\Ecommerce\Framework\Services\PaymentMethod\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;
use Carbon\Carbon;

class PaymentMethod extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'payment_method';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['total_price_discount_type', 'total_price_discount_value', 'total_price_discount_start_date', 'total_price_discount_end_date',  'active', 'percent_of_total', 'price', 'no_price_from', 'payment_external', 'title', 'shop_id', 'tax_rate_id', 'mollie_external_payment_way', 'order_confirmed_order_status_id', 'payment_completed_order_status_id',  'payment_failed_order_status_id', 'mollie_api_key', 'modified_by_user_id'];

    public function getPriceDetails()
    {
        $taxRate = 0;
        $priceInc = 0;
        
        if (isset($this->taxRate->rate)) {
            $taxRate = $this->taxRate->rate;
            $priceInc = (($this->taxRate->rate / 100) * $this->price) + $this->price;
        }

        return array(
            'original_price_ex_tax' => $this->price,
            'original_price_inc_tax' => $priceInc,
            'original_price_ex_tax_number_format' => number_format($this->price, 2, '.', ''),
            'original_price_inc_tax_number_forma' => number_format($priceInc, 2, '.', ''),
            'tax_rate' => $taxRate,
            'tax_value' => $priceInc - $this->price,
            'tax_value_number_format' => number_format(($priceInc - $this->price), 2, '.', ''),
            'currency' => $this->Shop->currency_code,

        );
    }

    public function taxRate()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\TaxRate\Entity\TaxRate');
    }

    public function orderConfirmedOrderStatus()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderStatus', 'order_confirmed_order_status_id');
    }

    public function orderPaymentCompletedOrderStatus()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderStatus', 'payment_completed_order_status_id');
    }

    public function orderPaymentFailedOrderStatus()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderStatus', 'payment_failed_order_status_id');
    }

    public function shop()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Shop\Entity\Shop');
    }

    public function setTotalPriceDiscountStartDateAttribute($value)
    {
        $this->attributes['total_price_discount_start_date'] = null;
        
        if ($value) {
            $date = explode('/', $value);
            $value = Carbon::createFromDate($date[2], $date[1], $date[0])->toDateTimeString();
            $this->attributes['total_price_discount_start_date'] = $value;
        }
    }

    public function getTotalPriceDiscountStartDateAttribute($value)
    {
        if ($value) {
            $date = explode('-', $value);
            return $date[2].'/'.$date[1].'/'.$date[0];
        }
        
        return null;
    }

    public function setTotalPriceDiscountEndDateAttribute($value)
    {
        $this->attributes['total_price_discount_end_date'] = null;

        if ($value) {
            $date = explode('/', $value);
            $value = Carbon::createFromDate($date[2], $date[1], $date[0])->toDateTimeString();
            $this->attributes['total_price_discount_end_date'] = $value;
        }
    }

    public function getTotalPriceDiscountEndDateAttribute($value)
    {
        if ($value) {
            $date = explode('-', $value);
            return $date[2].'/'.$date[1].'/'.$date[0];
        }
        
        return null;
    }
}