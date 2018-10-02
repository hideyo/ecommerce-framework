<?php 

namespace Hideyo\Ecommerce\Framework\Models;

use Hideyo\Ecommerce\Framework\Models\BaseModel;
use Carbon\Carbon;

class ProductAttribute extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product_attribute';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['product_id', 'reference_code', 'default_on', 'price', 'commercial_price', 'amount', 'tax_rate_id', 'discount_type', 'discount_value', 'discount_start_date', 'discount_end_date', 'discount_promotion', 'modified_by_user_id'];

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

    public function combinations()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Models\ProductAttributeCombination');
    }

    public function images()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Models\ProductAttributeImage');
    }

    public function productAttributeCombinations()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Models\ProductAttributeCombination');
    }

    public function taxRate()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\TaxRate');
    }

    public function product()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\Product\Entity\Product');
    }

    public function getPriceDetails()
    {  
        $price = $this->product->price;

        if ($this->price) {
            $price = $this->price;
        }

        $taxRate = 0;
        $priceInc = 0;
        $taxValue = 0;

        if (isset($this->taxRate->rate)) {
            $taxRate = $this->taxRate->rate;
            $priceInc = (($this->taxRate->rate / 100) * $price) + $price;
            $taxValue = $priceInc - $price;
        }

        $discountPriceInc = false;
        $discountPriceEx = false;
        $discountTaxRate = 0;
        if ($this->discount_value) {
            if ($this->discount_type == 'amount') {
                $discountPriceInc = $priceInc - $this->discount_value;
                $discountPriceEx = $discountPriceInc / 1.21;
            } elseif ($this->discount_type == 'percent') {
                $tax = ($this->discount_value / 100) * $priceInc;
                $discountPriceInc = $priceInc - $tax;
                $discountPriceEx = $discountPriceInc / 1.21;
            }


            $discountTaxRate = $discountPriceInc - $discountPriceEx;
            $discountPriceInc = $discountPriceInc;
            $discountPriceEx = $discountPriceEx;
        }

        $commercialPrice = null;
        if ($this->commercial_price) {
            $commercialPrice = number_format($this->commercial_price, 2, '.', '');
        }

        return array(
            'original_price_ex_tax'  => $price,
            'original_price_ex_tax_number_format'  => number_format($price, 2, '.', ''),
            'original_price_inc_tax' => $priceInc,
            'original_price_inc_tax_number_format' => number_format($priceInc, 2, '.', ''),
            'commercial_price_number_format' => $commercialPrice,
            'tax_rate' => $taxRate,
            'tax_value' => $taxValue,
            'currency' => 'EU',
            'discount_price_inc' => $discountPriceInc,
            'discount_price_inc_number_format' => number_format($discountPriceInc, 2, '.', ''),
            'discount_price_ex' => $discountPriceEx,
            'discount_price_ex_number_format' => number_format($discountPriceEx, 2, '.', ''),
            'discount_tax_value' => $discountTaxRate,
            'discount_value' => $this->discount_value,
            'amount' => $this->amount
        );
    }
}
