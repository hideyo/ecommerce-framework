<?php 

namespace Hideyo\Ecommerce\Framework\Models;

use Hideyo\Ecommerce\Framework\Models\BaseModel;

class AttributeGroup extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'attribute_group';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['type', 'default_value', 'title', 'filter', 'shop_id', 'modified_by_user_id'];

    public function categories()
    {
        return $this->belongsToMany('ProductCategory', 'product_category_related_extra_field');
    }

    public function attributes()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Models\Attribute');
    }
}
