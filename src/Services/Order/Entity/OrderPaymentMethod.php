<?php 

namespace Hideyo\Ecommerce\Framework\Services\Order\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;

class OrderPaymentMethod extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'order_payment_method';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['order_id', 'payment_method_id', 'title', 'price_with_tax', 'price_without_tax', 'tax_rate', 'tax_rate_id', 'percent_of_total', 'payment_external'];

    public function order()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Order\Entity\Order');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\paymentMethod\Entity\paymentMethod');
    }

    public function getPriceWithTaxNumberFormat()
    {
        return number_format($this->price_with_tax, 2);
    }

    public function getPriceWithoutTaxNumberFormat()
    {
        return number_format($this->price_without_tax, 2);
    }
}