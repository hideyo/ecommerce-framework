<?php 

namespace Hideyo\Models;

use Hideyo\Models\BaseModel;

class ProductWaitingList extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */        
    protected $table = 'product_waiting_list';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['product_id', 'product_attribute_id', 'email'];
    
    public function product()
    {
        return $this->belongsTo('Hideyo\Models\Product');
    }

    public function productAttribute()
    {
        return $this->belongsTo('Hideyo\Models\ProductAttribute');
    }
}