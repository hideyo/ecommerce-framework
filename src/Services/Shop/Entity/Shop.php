<?php 

namespace Hideyo\Ecommerce\Framework\Services\Shop\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;
use Cviebrock\EloquentSluggable\Sluggable;

class Shop extends BaseModel
{

    use Sluggable;

    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'shop';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['active', 'email', 'wholesale', 'currency_code', 'title', 'url', 'description', 'meta_title', 'meta_description', 'meta_keywords', 'logo_file_name', 'logo_file_path', 'thumbnail_square_sizes', 'thumbnail_widescreen_sizes'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title',
                'on_update'         => true,
                'unique'          => false
            ]
        ];
    }

    public function shops()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Services\Shop\Entity\Shop');
    }
    
    public function categories()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Services\ProductCategory\Entity\ProductCategory');
    }

    public function products()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Services\Product\Entity\Product');
    }

    public function setThumbnailSquareSizesAttribute($value = null)
    {
        $values  = explode(',', $value);
        $newValues = serialize($values);
        $this->attributes['thumbnail_square_sizes'] = $newValues;
    }

    public function getThumbnailSquareSizesAttribute($value = null)
    {
        if ($value) {
            $values = unserialize($value);
            $newValues  = implode(',', $values);
            return $newValues;
        }
    }

    public function setThumbnailWidescreenSizesAttribute($value = null)
    {
        $values  = explode(',', $value);
        $newValues = serialize($values);
        $this->attributes['thumbnail_widescreen_sizes'] = $newValues;
    }

    public function getThumbnailWidescreenSizesAttribute($value = null)
    {
        if ($value) {
            $values = unserialize($value);
            $newValues  = implode(',', $values);
            return $newValues;
        }
    }
}
