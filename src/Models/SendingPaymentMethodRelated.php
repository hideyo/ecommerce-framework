<?php 

namespace Hideyo\Ecommerce\Framework\Models;

use Hideyo\Ecommerce\Framework\Models\BaseModel;

class SendingPaymentMethodRelated extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'sending_payment_method_related';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['email_order_subject', 'email_order_body', 'pdf_text', 'payment_text', 'payment_confirmed_text'];

    public function sendingMethod()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\SendingMethod');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\PaymentMethod');
    }
}