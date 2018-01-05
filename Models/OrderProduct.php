<?php 

namespace Hideyo\Models;

use Hideyo\Models\BaseModel;

class OrderProduct extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'order_product';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['order_id', 'product_id', 'title', 'product_attribute_id', 'product_attribute_title', 'reference_code', 'tax_rate_id', 'tax_rate', 'price_with_tax', 'price_without_tax', 'total_price_with_tax', 'total_price_without_tax', 'amount', 'weight', 'original_price_with_tax', 'original_price_without_tax', 'original_total_price_with_tax', 'original_total_price_without_tax'];

    public function order()
    {
        return $this->belongsTo('Hideyo\Models\Order');
    }

    public function product()
    {
        return $this->belongsTo('Hideyo\Models\Product');
    }

    public function getOriginalPriceWithTaxNumberFormat()
    {
        return number_format($this->original_price_with_tax, 2, '.', '');
    }

    public function getOriginalPriceWithoutTaxNumberFormat()
    {
        return number_format($this->original_price_without_tax, 2, '.', '');
    }

    public function getOriginalTotalPriceWithTaxNumberFormat()
    {
        return number_format($this->original_total_price_with_tax, 2, '.', '');
    }

    public function getOriginalTotalPriceWithoutTaxNumberFormat()
    {
        return number_format($this->original_total_price_without_tax, 2, '.', '');
    }

    public function getPriceWithTaxNumberFormat()
    {
        return number_format($this->price_with_tax, 2, '.', '');
    }

    public function getPriceWithoutTaxNumberFormat()
    {
        return number_format($this->price_without_tax, 2, '.', '');
    }

    public function getTotalPriceWithoutTaxNumberFormat()
    {
        return number_format($this->total_price_without_tax, 2, '.', '');
    }

    public function getTotalPriceWithTaxNumberFormat()
    {
        return number_format($this->total_price_with_tax, 2, '.', '');
    }
    
    public function productAttribute()
    {
        return $this->belongsTo('Hideyo\Models\ProductAttribute');
    }
}
