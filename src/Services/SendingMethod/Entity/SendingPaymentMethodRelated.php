<?php
namespace Hideyo\Ecommerce\Framework\Services\SendingMethod\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;

class SendingPaymentMethodRelated extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'sending_payment_method_related';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = [];

    public function sendingMethod()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\SendingMethod\Entity\SendingMethod');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\PaymentMethod\Entity\PaymentMethod');
    }
}