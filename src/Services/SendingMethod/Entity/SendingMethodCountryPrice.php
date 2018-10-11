<?php 

namespace Hideyo\Ecommerce\Framework\Services\SendingMethod\Entity;

use Hideyo\Ecommerce\Framework\Services\BaseModel;
use Carbon\Carbon;

class SendingMethodCountryPrice extends BaseModel
{

    /**
     * Model: SendingPaymentMethodRelated
     * Note: please keep models thin. Put logic not in models,
     * Information about models in Laravel: http://laravel.com/docs/5.1/eloquent
     * @author     Matthijs Neijenhuijs <matthijs@dutchbridge.nl>
     * @copyright  DutchBridge - dont share/steel!
     */

    protected $table = 'sending_method_country_price';

    // Add the 'avatar' attachment to the fillable array so that it's mass-assignable on this model.
    protected $fillable = ['country_code', 'total_price_discount_type', 'total_price_discount_value', 'total_price_discount_start_date', 'total_price_discount_end_date', 'price', 'no_price_from', 'minimal_weight', 'maximal_weight', 'name', 'sending_method_id', 'tax_rate_id', 'iso_3166_2', 'iso_3166_3'];

    public function sendingMethod()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\SendingMethod\Entity\SendingMethod');
    }

    public function getPriceDetails()
    {
        $taxRate = 0;
        $price_inc = 0;

        if (isset($this->taxRate->rate)) {
            $taxRate = $this->taxRate->rate;
            $price_inc = round((($this->taxRate->rate / 100) * $this->price) + $this->price, 2, PHP_ROUND_HALF_UP);
        }

        return array(
            'original_price_ex_tax' => $this->price,
            'original_price_inc_tax' => $price_inc,
            'original_price_ex_tax_number_format' => number_format($this->price, 2, '.', ''),
            'original_price_inc_tax_number_format' => number_format($price_inc, 2, '.', ''),
            'no_price_from' => $this->no_price_from,
            'no_price_from_number_format' => number_format($this->no_price_from, 0),
            'tax_rate' => $taxRate,
            'tax_value' => $price_inc - $this->price,
            'tax_value_number_format' => number_format(($price_inc - $this->price), 2, '.', '')

        );
    }



    public function taxRate()
    {
        return $this->belongsTo('Hideyo\Ecommerce\Framework\Services\TaxRate\Entity\TaxRate');
    }
}