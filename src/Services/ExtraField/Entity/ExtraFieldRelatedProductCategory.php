<?php 

namespace Hideyo\Ecommerce\Framework\Models;

use Hideyo\Ecommerce\Framework\Services\BaseModel;

class ExtraFieldRelatedProductCategory extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'extra_field_related_product_category';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['extra_field_id', 'product_category_id'];

    public function extraField()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\ExtraField', 'extra_field_id');
    }

    public function relatedProductCategory()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\ProductCategory', 'product_category_id');
    }
}