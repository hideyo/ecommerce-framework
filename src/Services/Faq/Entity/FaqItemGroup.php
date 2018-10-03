<?php 

namespace Hideyo\Ecommerce\Framework\Services\Faq\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;
use Cviebrock\EloquentSluggable\Sluggable;

class FaqItemGroup extends BaseModel
{
    use Sluggable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'faq_item_group';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['id', 'title', 'meta_title', 'meta_description', 'meta_keywords', 'shop_id'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function faqItems()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Models\FaqItem');
    }
}
