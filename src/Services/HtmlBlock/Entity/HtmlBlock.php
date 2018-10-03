<?php 

namespace Hideyo\Ecommerce\Framework\Services\HtmlBlock\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;
use Cviebrock\EloquentSluggable\Sluggable;

class HtmlBlock extends BaseModel
{
    use Sluggable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'html_block';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['id', 'active', 'title', 'short_title', 'button_title', 'position', 'url', 'content', 'template', 'thumbnail_height', 'thumbnail_width', 'image_file_name', 'image_file_path', 'image_file_extension', 'image_file_size', 'shop_id'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }
}