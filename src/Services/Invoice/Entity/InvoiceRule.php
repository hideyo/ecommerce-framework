<?php 

namespace Hideyo\Ecommerce\Framework\Models;

use Hideyo\Ecommerce\Framework\Services\BaseModel;

class InvoiceRule extends BaseModel
{
    protected $table = 'invoice_rule';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['invoice_id', 'product_id', 'title', 'product_attribute_id', 'product_attribute_title', 'reference_code', 'tax_rate_id', 'tax_rate', 'price_with_tax', 'price_without_tax', 'total_price_with_tax', 'total_price_without_tax', 'amount', 'weight'];

    public function __construct(array $attributes = array())
    {
        $this->table = $this->table;        
        parent::__construct($attributes);
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

    public function invoice()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\Invoice');
    }

    public function productAttribute()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\ProductAttribute');
    }
}
