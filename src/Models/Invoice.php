<?php 

namespace Hideyo\Ecommerce\Framework\Models;

use Hideyo\Ecommerce\Framework\Models\BaseModel;
use Carbon\Carbon;

class Invoice extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */    
    protected $table = 'invoice';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['order_id', 'type', 'client_id', 'shop_id', 'price_with_tax', 'price_without_tax', 'bill_invoice_address_id', 'delivery_invoice_address_id',];

    public static function boot()
    {
        static::creating(function ($model) {

            $order = Invoice::whereNotNull('client_id')->where('shop_id', '=', $model->shop_id)->where('created_at', '>=', Carbon::now()->year)->orderBy('created_at', 'desc')->first();
            $shop = Shop::find($model->shop_id);
            $prefix = strtoupper(substr($shop->slug, 0, 4));
    
            if ($order) {
                $model->generated_year_invoice_id = $order->generated_year_invoice_id + 1;
                $model->generated_custom_invoice_id = $order->generated_year_invoice_id + 1;
                $model->generated_custom_invoice_id = $prefix.Carbon::now()->format('y').$model->generated_custom_invoice_id;
            } else {
                $model->generated_year_invoice_id = 1;
                $model->generated_custom_invoice_id = $prefix.Carbon::now()->format('y').'1';
            }
        });

        parent::boot();
    }


    public function products()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Models\InvoiceRule');
    }

    public function client()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\Client');
    }

    public function order()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Models\Order');
    }

    public function invoiceAddress()
    {
        return $this->hasMany('Hideyo\Ecommerce\Framework\Models\InvoiceAddress');
    }

    public function invoiceDeliveryAddress()
    {
        return $this->hasOne('Hideyo\Ecommerce\Framework\Models\InvoiceAddress', 'id', 'delivery_invoice_address_id');
    }

    public function invoiceBillAddress()
    {
        return $this->hasOne('Hideyo\Ecommerce\Framework\Models\InvoiceAddress', 'id', 'bill_invoice_address_id');
    }

    public function taxTotal()
    {
        return number_format($this->price_with_tax - $this->price_without_tax, 2, '.', '');
    }

    public function getTotalDiscountNumberFormat()
    {
        return number_format($this->total_discount, 2, '.', '');
    }

    public function getPriceWithTaxNumberFormat()
    {
        return number_format($this->price_with_tax, 2, '.', '');
    }

    public function getPriceWithTaxNoFormat()
    {
        return $this->price_with_tax;
    }

    public function getPriceWithoutTaxNumberFormat()
    {
        return number_format($this->price_without_tax, 2, '.', '');
    }

    public function taxDetails()
    {
        $taxArray = array();

        if ($this->products) {
            foreach ($this->products as $product) {
                if (!isset($taxArray[$product->tax_rate])) {
                    $taxArray[$product->tax_rate] = "";
                }
                $taxArray[$product->tax_rate] += $product->total_price_with_tax - $product->total_price_without_tax;
            }
        }

        if ($this->invoiceSendingMethod) {
            if (!isset($taxArray[$this->invoiceSendingMethod->tax_rate])) {
                $taxArray[$this->invoiceSendingMethod->tax_rate] = "";
            }
                $taxArray[$this->invoiceSendingMethod->tax_rate] += $this->invoiceSendingMethod->price_with_tax - $this->invoiceSendingMethod->price_without_tax;
        }

        if ($this->invoicePaymentMethod) {
            if (!isset($taxArray[$this->invoicePaymentMethod->tax_rate])) {
                $taxArray[$this->invoicePaymentMethod->tax_rate] = "";
            }
                $taxArray[$this->invoicePaymentMethod->tax_rate] += $this->invoicePaymentMethod->price_with_tax - $this->invoicePaymentMethod->price_without_tax;
        }

        return $taxArray;
    }
}
