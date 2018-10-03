<?php 

namespace Hideyo\Ecommerce\Framework\Services\Brand\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;

use Cviebrock\EloquentSluggable\Sluggable;

class Brand extends BaseModel
{
    use Sluggable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'brand';

    protected $scoped = array('shop_id');

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['active',  'title', 'short_description', 'description', 'rank', 'meta_title', 'meta_description', 'meta_keywords', 'shop_id', 'modified_by_user_id'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function products()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Models\Product');
    }

    public function brandImages()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Models\BrandImage');
    }

    public function shop()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Shop\Entity\Shop');
    }
}
