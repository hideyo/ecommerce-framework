<?php 

namespace Hideyo\Ecommerce\Framework\Models;

use Hideyo\Ecommerce\Framework\Models\BaseModel;
use Cviebrock\EloquentSluggable\Sluggable;
use Carbon\Carbon;

class News extends BaseModel
{
    use Sluggable;

    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'news';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['id', 'news_group_id', 'title', 'short_description', 'content', 'published_at', 'meta_title', 'meta_description', 'meta_keywords', 'shop_id'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    public function setPublishedAtAttribute($value)
    {
        $this->attributes['published_at'] = null;

        if ($value) {
            $date = explode('/', $value);
            $this->attributes['published_at'] = $value;

            if (isset($date[2])) {
                $value = Carbon::createFromDate($date[2], $date[1], $date[0])->toDateTimeString();
                $this->attributes['published_at'] = $value;
            }
        }
    }

    public function getPublishedAtAttribute($value)
    {
        if ($value) {
            $date = explode('-', $value);
            return $date[2].'/'.$date[1].'/'.$date[0];
        }
        
        return null;
    }

    public function newsImages()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Models\NewsImage');
    }

    public function newsGroup()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\NewsGroup');
    }
}
