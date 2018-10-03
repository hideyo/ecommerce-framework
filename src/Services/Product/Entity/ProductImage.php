<?php 

namespace Hideyo\Ecommerce\Framework\Services\Product\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;

class ProductImage extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_image';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['product_id', 'product_variation_id', 'file', 'extension', 'size', 'path', 'rank', 'tag', 'shop_id', 'modified_by_user_id',];

    public function product()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Product\Entity\Product');
    }

    public function relatedProductAttributes()
    {
        return $this->belongsToMany('Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductAttribute', 
            'product_attribute_image', 
            'product_image_id', 
            'product_attribute_id');
    }

    public function relatedAttributes()
    {
        return $this->belongsToMany('Hideyo\Ecommerce\Framework\Services\Attribute\Entity\Attribute', 
            'product_image_attribute', 
            'product_image_id', 
            'attribute_id');
    }

    public function productImageAttributes()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductImageAttribute');
    }

    public function productAttributeImages()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductAttributeImage');
    }
    
}