<?php 

namespace Hideyo\Ecommerce\Framework\Services\Content\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;

class ContentImage extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'content_image';

    protected $guarded =  array('file');

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['content_id', 'file', 'extension', 'size', 'path', 'rank', 'tag', 'modified_by_user_id',];
}