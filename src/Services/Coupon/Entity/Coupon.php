<?php 

namespace Hideyo\Ecommerce\Framework\Services\Coupon\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;
use Carbon\Carbon;

class Coupon extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'coupon';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['active', 'permanent', 'coupon_group_id', 'title', 'value', 'code', 'type', 'discount_way', 'published_at', 'unpublished_at', 'shop_id', 'modified_by_user_id'];

    public function products()
    {
        return $this->belongsToMany('Hideyo\Ecommerce\Framework\Services\Product\Entity\Product', 'coupon_product');
    }

    public function couponGroup()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Coupon\Entity\CouponGroup');
    }

    public function productCategories()
    {
        return $this->belongsToMany('Hideyo\Ecommerce\Framework\Services\ProductCategory\Entity\ProductCategory', 'coupon_product_category');
    }

    public function sendingMethods()
    {
        return $this->belongsToMany('Hideyo\Ecommerce\Framework\Services\SendingMethod\Entity\SendingMethod', 'coupon_sending_method');
    }

    public function paymentMethods()
    {
        return $this->belongsToMany('Hideyo\Ecommerce\Framework\Services\PaymentMethod\Entity\PaymentMethod', 'coupon_payment_method');
    }

    public function setPublishedAtAttribute($value)
    {
        $this->attributes['published_at'] = null;
        
        if ($value) {
            $date = explode('/', $value);
            $value = Carbon::createFromDate($date[2], $date[1], $date[0])->toDateTimeString();
            $this->attributes['published_at'] = $value;
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

    public function setUnPublishedAtAttribute($value)
    {
        $this->attributes['unpublished_at'] = null;

        if ($value) {
            $date = explode('/', $value);
            $value = Carbon::createFromDate($date[2], $date[1], $date[0])->toDateTimeString();
            $this->attributes['unpublished_at'] = $value;
        }
    }

    public function getUnPublishedAtAttribute($value)
    {
        if ($value) {
            $date = explode('-', $value);
            return $date[2].'/'.$date[1].'/'.$date[0];
        }
        
        return null;
    }
}
