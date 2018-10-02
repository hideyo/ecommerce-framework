<?php 

namespace Hideyo\Ecommerce\Framework\Models;

use Hideyo\Ecommerce\Framework\Models\BaseModel;

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
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Shop\Entity\Shop');
    }

    public function product()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\Product');
    }

    public function productCategory()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\ProductCategory');
    }
}
