<?php 

namespace Hideyo\Models;

use Hideyo\Models\BaseModel;

class ProductCategoryHighlightProduct extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_category_highlight_product';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['product_id', 'product_category_id'];

    public function product()
    {
        return $this->belongsTo('Hideyo\Models\Product', 'product_id');
    }

    public function productCategory()
    {
        return $this->belongsTo('Hideyo\Models\ProductCategory', 'product_category_id');
    }
}