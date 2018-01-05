<?php 

namespace Hideyo\Models;

class NewsImage extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */        
    protected $table = 'news_image';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['news_id', 'file', 'extension', 'size', 'path', 'rank', 'tag', 'modified_by_user_id',];

    public function news()
    {
        return $this->belongsTo('Hideyo\Models\News');
    }
}