<?php 

namespace Hideyo\Models;

use Hideyo\Models\BaseModel;

class ProductSubProductCategory extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_sub_product_category';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['category_id', 'product_id'];

    public function shop()
    {
        return $this->belongsTo('Hideyo\Models\Shop');
    }

    public function product()
    {
        return $this->belongsTo('Hideyo\Models\Product');
    }

    public function productCategory()
    {
        return $this->belongsTo('Hideyo\Models\ProductCategory');
    }
}
