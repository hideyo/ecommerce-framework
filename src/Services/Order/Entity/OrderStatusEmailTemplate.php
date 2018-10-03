<?php 

namespace Hideyo\Ecommerce\Framework\Services\Order\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;

class OrderStatusEmailTemplate extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'order_status_email_template';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['title', 'subject', 'content', 'shop_id', 'modified_by_user_id'];
}