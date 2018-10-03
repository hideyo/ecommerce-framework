<?php 

namespace Hideyo\Ecommerce\Framework\Services\Product\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;
use Carbon\Carbon;

class ProductAmountSeries extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_amount_series';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['active', 'product_id',  'series_value', 'series_start', 'series_max', 'modified_by_user_id'];

    public function product()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Product\Entity\Product');
    }

    public function range()
    {

        $range = range($this->series_start, $this->series_max, $this->series_value);
        return array_combine(array_keys(array_flip($range)), $range);
    }
}