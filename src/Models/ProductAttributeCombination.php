<?php 

namespace Hideyo\Ecommerce\Framework\Models;

use Hideyo\Ecommerce\Framework\Models\BaseModel;

class ProductAttributeCombination extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_attribute_combination';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['product_attribute_id', 'attribute_id',  'modified_by_user_id'];

    public function attribute()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\Attribute');
    }

    public function productAttribute()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\ProductAttribute');
    }
}
