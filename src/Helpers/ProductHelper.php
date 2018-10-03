<?php

namespace Hideyo\Ecommerce\Framework\Helpers;

use Config;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\Product;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductAttribute;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductAttributeCombination;
use DbView;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductImage;
use Illuminate\Support\Facades\Request;

class ProductHelper
{
    public static function getProductAttributeId($productId, $attributeIds) 
    {
        $productAttributeResultWithAttributeId =  ProductAttribute::
        whereHas('combinations', function ($query) use ($attributeIds) {    
        print_r($attributeIds);               
            $query->whereIn('attribute_id', $attributeIds);              
        })
        ->with('combinations')
        ->where('product_attribute.product_id', '=', $productId)
        ->get();

        if($productAttributeResultWithAttributeId) {
            return $productAttributeResultWithAttributeId->toArray();
        }

        return false;  
    }
  
    public static function getImage($productId, $combinationsIds, $productAttributeId = false)
    {
        $product = new Product();
        $product = $product->has('productImages')->where('id', '=', $productId)->first();
        $images = array();

        if($product AND $product->productImages->count()) {  

            $images = $product->productImages()->has('relatedAttributes', '=', 0)->has('relatedProductAttributes', '=', 0)->orderBy('rank', '=', 'asc')->get();

            if($combinationsIds) {

                $imagesRelatedAttributes = ProductImage::
                whereHas('relatedAttributes', function($query) use ($combinationsIds, $product, $productId) { $query->with(array('productImage'))->whereIn('attribute_id', $combinationsIds); });
                
                $images = $images->merge($imagesRelatedAttributes)->sortBy('rank');          
            }

            if($productAttributeId) {                
                $imagesRelatedAttributes = ProductImage::
                whereHas('relatedProductAttributes', function($query) use ($productAttributeId, $product) { $query->where('product_attribute_id', '=', $productAttributeId); });
            }

            if($combinationsIds OR $productAttributeId) {
                $imagesRelatedAttributes->where('product_id', '=', $productId)->get();
                $images = $images->merge($imagesRelatedAttributes)->sortBy('rank');
            }

            if(!$images->count()) {
                $images = $product->productImages()->orderBy('rank', '=', 'asc')->get();
            }

            if ($images->count()) {
                return $images->first()->file;
            }
        }
    }

    public static function priceDetails($product, $field)
    {
        $preSaleDiscount = session()->get('preSaleDiscount');

        if ($product->price) {

            $taxRate = 0;
            $priceInc = 0;
            $taxValue = 0;

            if (isset($product->taxRate)) {
                $taxRate = $product->taxRate->rate;        
                $priceInc = (($product->taxRate->rate / 100) * $product->price) + $product->price;
                $taxValue = $priceInc - $product->price;
            }

            $discountPriceInc = $priceInc;
            $discountPriceEx = $product->price;
            $discountTaxRate = 0;

            if($preSaleDiscount) {

                if ($preSaleDiscount['value'] AND $preSaleDiscount['collection_id'] == $product->collection_id) {

                    if ($preSaleDiscount['discount_way'] == 'amount') {
                        $discountPriceInc = $priceInc - $product->value;
                        $discountPriceEx = $discountPriceInc / 1.21;
                    } elseif ($preSaleDiscount['discount_way'] == 'percent') {
          
                        $tax = ($preSaleDiscount['value'] / 100) * $priceInc;
                        $discountPriceInc = $priceInc - $tax;
                        $discountPriceEx = $discountPriceInc / 1.21;                       
                    }
                    $discountTaxRate = $discountPriceInc - $discountPriceEx;                   
                }

                if($preSaleDiscount['products']) {

                    $productIds = array_column($preSaleDiscount['products'], 'id');

                    if (in_array($product->id, $productIds) OR (isset($product->product_id) AND in_array($product->product_id, $productIds))) {

                        if ($preSaleDiscount['discount_way'] == 'amount') {
                            $discountPriceInc = $priceInc - $product->value;
                            $discountPriceEx = $discountPriceInc / 1.21;
                        } elseif ($preSaleDiscount['discount_way'] == 'percent') {
              
                            $tax = ($preSaleDiscount['value'] / 100) * $priceInc;
                            $discountPriceInc = $priceInc - $tax;
                            $discountPriceEx = $discountPriceInc / 1.21;                       
                        }
                        $discountTaxRate = $discountPriceInc - $discountPriceEx;
                    }

                }
            } else {
                if ($product->discount_value) {
                    if ($product->discount_type == 'amount') {

                        $discountPriceInc = $priceInc - $product->discount_value;
                        $discountPriceEx = $discountPriceInc / 1.21;
                    } elseif ($product->discount_type == 'percent') {

                        $tax = ($product->discount_value / 100) * $priceInc;
                        $discountPriceInc = $priceInc - $tax;
                        $discountPriceEx = $discountPriceInc / 1.21;
                    }
                    $discountTaxRate = $discountPriceInc - $discountPriceEx;
                }
            }

            $productArray = array(
                'original_price_ex_tax'  => $product->price,
                'original_price_ex_tax_number_format'  => number_format($product->price, 2, '.', ''),
                'original_price_inc_tax' => $priceInc,
                'original_price_inc_tax_number_format' => number_format($priceInc, 2, '.', ''),
                'tax_rate' => $taxRate,
                'tax_value' => $taxValue,
                'currency' => 'EU',
                'discount_price_inc' => $discountPriceInc,
                'discount_price_inc_number_format' => number_format($discountPriceInc, 2, '.', ''),
                'discount_price_ex' => $discountPriceEx,
                'discount_price_ex_number_format' => number_format($discountPriceEx, 2, '.', ''),
                'discount_tax_value' => $discountTaxRate,
                'discount_value' => $product->discount_value,
                'amount' => $product->amount
            );
        
            if (isset($productArray[$field])) {
                return $productArray[$field];
            }         
        } 

        return false;
    }
}