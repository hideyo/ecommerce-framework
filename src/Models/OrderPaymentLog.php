<?php 

namespace Hideyo\Ecommerce\Framework\Models;

use Hideyo\Ecommerce\Framework\Models\BaseModel;

class OrderPaymentLog extends BaseModel
{
    protected $table = 'order_payment_log';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['order_id', 'type', 'log'];

    public function order()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\Order');
    }
}