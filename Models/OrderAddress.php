<?php 

namespace Hideyo\Models;

use Hideyo\Models\BaseModel;

class OrderAddress extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'order_address';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['company', 'phone', 'gender', 'initials', 'firstname', 'lastname', 'street', 'housenumber', 'housenumber_suffix', 'zipcode', 'city', 'country', 'order_id', 'modified_by_user_id'];

    public function order()
    {
        return $this->belongsTo('Hideyo\Models\Order');
    }
}