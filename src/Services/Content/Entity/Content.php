<?php 

namespace Hideyo\Ecommerce\Framework\Services\Content\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;
use Cviebrock\EloquentSluggable\Sluggable;

class Content extends BaseModel
{
    use Sluggable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'content';

    protected $sluggable = array(
        'build_from'        => 'title',
        'save_to'           => 'slug',
        'on_update'         => true,
    );

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['id', 'active', 'content_group_id', 'title', 'content', 'meta_title', 'meta_description', 'meta_keywords', 'shop_id'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function contentGroup()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Content\Entity\ContentGroup');
    }

    public function contentImages()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Services\Content\Entity\ContentImage');
    }
}
