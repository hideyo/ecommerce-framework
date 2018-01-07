<?php 

namespace Hideyo\Ecommerce\Framework\Models;

use Hideyo\Ecommerce\Framework\Models\BaseModel;

class OrderSendingMethod extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'order_sending_method';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['order_id', 'sending_method_id', 'title', 'price_with_tax', 'price_without_tax', 'tax_rate', 'weight', 'tax_rate_id'];

    public function order()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\Order');
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