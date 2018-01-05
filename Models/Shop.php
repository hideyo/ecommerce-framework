<?php 

namespace Hideyo\Models;

use Hideyo\Models\BaseModel;
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

    protected $sluggable = array(
        'build_from'        => 'title',
        'save_to'           => 'slug',
        'on_update'         => true,
    );

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function shops()
    {
        return $this->hasMany('Hideyo\Models\Shop');
    }
    
    public function categories()
    {
        return $this->hasMany('Hideyo\Models\ProductCategory');
    }

    public function products()
    {
        return $this->hasMany('Hideyo\Models\Product');
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
