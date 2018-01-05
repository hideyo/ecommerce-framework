<?php 

namespace Hideyo\Models;

use Hideyo\Models\BaseModel;
use Cviebrock\EloquentSluggable\Sluggable;

class FaqItem extends BaseModel {

    use Sluggable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'faq_item';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['id', 'faq_item_group_id', 'question', 'answer', 'slug', 'shop_id'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'question'
            ]
        ];
    }

    public function faqItemGroup()
    {
        return $this->belongsTo('Hideyo\Models\faqItemGroup');
    }
}