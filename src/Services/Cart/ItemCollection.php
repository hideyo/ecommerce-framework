<?php

namespace Hideyo\Ecommerce\Framework\Services\Cart;

use Illuminate\Support\Collection;
use Hideyo\Ecommerce\Framework\Services\Cart\Helpers\Helpers;

class ItemCollection extends Collection {
    /**
     * Sets the config parameters.
     *
     * @var
     */
    protected $config;

   /**
     * ItemCollection constructor.
     * @param array|mixed $items
     */
    public function __construct($items, $config)
    {
        parent::__construct($items);
        $this->config = $config;
    }


    /**
     * get the sum of tax of the original price
     *
     * @return mixed|null
     */
    public function getOriginalPriceTaxSum($formatted = true)
    {
        return $this->getOriginalPriceWithTaxSum() - $this->getOriginalPriceWithoutTaxSum();
    }


    /**
     * get the sum of original price without tax
     *
     * @return mixed|null
     */
    public function getOriginalPriceWithoutTaxSum($formatted = true)
    {
        return Helpers::formatValue($this->quantity * $this->attributes['price_details']['original_price_ex_tax'], $formatted, $this->config);
    }


    /**
     * get the sum of original price with tax
     *
     * @return mixed|null
     */
    public function getOriginalPriceWithTaxSum($formatted = true)
    {
        return Helpers::formatValue($this->quantity * $this->getOriginalPriceWithTaxAndConditions(false), $formatted, $this->config);
    }


    /**
     * get the original price without tax and conditions
     *
     * @return mixed|null
     */
    public function getOriginalPriceWithoutTaxAndConditions($formatted = true)
    {
        $originalPrice = $this->attributes['price_details']['original_price_ex_tax'];
        $newPrice = 0.00;
        $processed = 0;

        if( $this->hasConditions() )
        {
            if( is_array($this->conditions) )
            {
                foreach($this->conditions as $condition)
                {
                    if( $condition->getTarget() === 'item' )
                    {
                        ( $processed > 0 ) ? $toBeCalculated = $newPrice : $toBeCalculated = $originalPrice;
                        $newPrice = $condition->applyCondition($toBeCalculated);
                        $processed++;
                    }
                }
            }
            else
            {
                if( $this['conditions']->getTarget() === 'item' )
                {
                    $newPrice = $this['conditions']->applyCondition($originalPrice);
                }
            }
            return Helpers::formatValue($newPrice, $formatted, $this->config);
        }

        return Helpers::formatValue($originalPrice, $formatted, $this->config);
    }


    /**
     * get the original price with tax and conditions
     *
     * @return mixed|null
     */
    public function getOriginalPriceWithTaxAndConditions($formatted = true)
    {
        $originalPrice = $this->attributes['price_details']['original_price_inc_tax'];
        $newPrice = 0.00;
        $processed = 0;

        if( $this->hasConditions() )
        {
            if( is_array($this->conditions) )
            {
                foreach($this->conditions as $condition)
                {
                    if( $condition->getTarget() === 'item' )
                    {
                        ( $processed > 0 ) ? $toBeCalculated = $newPrice : $toBeCalculated = $originalPrice;
                        $newPrice = $condition->applyCondition($toBeCalculated);
                        $processed++;
                    }
                }
            }
            else
            {
                if( $this['conditions']->getTarget() === 'item' )
                {
                    $newPrice = $this['conditions']->applyCondition($originalPrice);
                }
            }
            return Helpers::formatValue($newPrice, $formatted, $this->config);
        }

        return Helpers::formatValue($originalPrice, $formatted, $this->config);
    }


    /**
     * get the original price with tax
     *
     * @return mixed|null
     */
    public function getOriginalPriceWithTax($formatted = true)
    {
        $originalPrice = $this->attributes['price_details']['original_price_inc_tax'];
        return Helpers::formatValue($originalPrice, $formatted, $this->config);
    }


    /**
     * get the price without tax
     *
     * @return mixed|null
     */
    public function getOriginalPriceWithoutTax($formatted = true)
    {
        return Helpers::formatValue($this->attributes['price_details']['original_price_ex_tax'], $formatted, $this->config);
    }

    public function __get($name)
    {
        if( $this->has($name) ) return $this->get($name);
        return null;
    }

    /**
     * check if item has conditions
     *
     * @return bool
     */
    public function hasConditions()
    {
        if( ! isset($this['conditions']) ) return false;
        if( is_array($this['conditions']) )
        {
            return count($this['conditions']) > 0;
        }
        $conditionInstance = "Hideyo\\Services\\Cart\\CartCondition";
        if( $this['conditions'] instanceof $conditionInstance ) return true;
        return false;
    }
    
    /**
     * check if item has conditions
     *
     * @return mixed|null
     */
    public function getConditions()
    {
        if(! $this->hasConditions() ) return [];
        return $this['conditions'];
    }
}