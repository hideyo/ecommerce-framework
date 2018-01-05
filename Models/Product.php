<?php 

namespace Hideyo\Models;

use Hideyo\Models\BaseModel;
use Cviebrock\EloquentSluggable\Sluggable;
use Carbon\Carbon;
use Elasticquent\ElasticquentTrait;

class Product extends BaseModel
{
    use ElasticquentTrait, Sluggable;

    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'product';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['active', 'discount_promotion', 'discount_type', 'discount_value', 'discount_start_date', 'discount_end_date', 'title', 'brand_id', 'product_category_id', 'reference_code', 'ean_code', 'mpn_code', 'short_description', 'description', 'ingredients', 'price', 'commercial_price', 'tax_rate_id', 'amount', 'meta_title', 'meta_description', 'meta_keywords', 'shop_id', 'modified_by_user_id', 'weight', 'leading_atrribute_group_id', 'rank'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title'
            ]
        ];
    }

    function getIndexName()
    {
        return 'product';
    }
    
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

    public function setRankAttribute($value)
    {       
        if (is_null($value)) {
            $value = 0;
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

    public function getPriceDetails()
    {
        if ($this->price) {
            $taxRate = 0;
            $priceInc = 0;
            $taxValue = 0;

            if (isset($this->taxRate->rate)) {
                $taxRate = $this->taxRate->rate;
                $priceInc = (($this->taxRate->rate / 100) * $this->price) + $this->price;
                $taxValue = $priceInc - $this->price;
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
                'original_price_ex_tax'  => $this->price,
                'original_price_ex_tax_number_format'  => number_format($this->price, 2, '.', ''),
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
        
        return null;    
    }

    public function shop()
    {
        return $this->belongsTo('Hideyo\Models\Shop');
    }

    public function attributeGroup()
    {
        return $this->belongsTo('Hideyo\Models\AttributeGroup', 'leading_atrribute_group_id');
    }
    
    public function extraFields()
    {
        return $this->hasMany('Hideyo\Models\ProductExtraFieldValue');
    }

    public function taxRate()
    {
        return $this->belongsTo('Hideyo\Models\TaxRate');
    }

    public function brand()
    {
        return $this->belongsTo('Hideyo\Models\Brand');
    }

    public function productCategory()
    {
        return $this->belongsTo('Hideyo\Models\ProductCategory');
    }

    public function subcategories()
    {
        return $this->belongsToMany('Hideyo\Models\ProductCategory', 'product_sub_product_category');
    }

    public function relatedProducts()
    {
        return $this->belongsToMany('Hideyo\Models\Product', 'product_related_product', 'product_id', 'related_product_id');
    }

    public function relatedProductsActive()
    {
        return $this->belongsToMany('Hideyo\Models\Product', 'product_related_product', 'product_id', 'related_product_id')->whereHas('productCategory', function ($query) {
            $query->where('active', '=', '1');
        })->where('product.active', '=', '1');
    }

    public function productImages()
    {
        return $this->hasMany('Hideyo\Models\ProductImage');
    }

    public function attributes()
    {
        return $this->hasMany('Hideyo\Models\ProductAttribute');
    }

    public function amountOptions()
    {
        return $this->hasMany('Hideyo\Models\ProductAmountOption');
    }

    public function amountSeries()
    {
        return $this->hasMany('Hideyo\Models\ProductAmountSeries');
    }
}
