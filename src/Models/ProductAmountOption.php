<?php 

namespace Hideyo\Ecommerce\Framework\Models;

use Hideyo\Ecommerce\Framework\Models\BaseModel;
use Carbon\Carbon;

class ProductAmountOption extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_amount_option';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['product_id',  'amount', 'discount_type', 'discount_value', 'discount_start_date', 'discount_end_date', 'modified_by_user_id'];



    public function setDiscountStartDateAttribute($value)
    {
        $this->attributes['discount_start_date'] = null;

        if ($value) {
            $date = explode('/', $value);

            $value = Carbon::createFromDate($date[2], $date[1], $date[0])->toDateTimeString();
            $this->attributes['discount_start_date'] = $value;
        }
    }

    public function getDiscountStartDateAttribute($value)
    {
        if ($value) {
            $date = explode('-', $value);
            return $date[2].'/'.$date[1].'/'.$date[0];
        }
        
        return null;
    }

    public function setDiscountEndDateAttribute($value)
    {
        $this->attributes['discount_end_date'] = null;

        if ($value) {
            $date = explode('/', $value);
            $value = Carbon::createFromDate($date[2], $date[1], $date[0])->toDateTimeString();
            $this->attributes['discount_end_date'] = $value;
        }
    }

    public function getDiscountEndDateAttribute($value)
    {
        if ($value) {
            $date = explode('-', $value);
            return $date[2].'/'.$date[1].'/'.$date[0];
        }
        
        return null;
    }

    public function product()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\Product');
    }
}