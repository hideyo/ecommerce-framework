<?php
namespace Hideyo\Ecommerce\Framework\Services\ExtraField\Entity;

use Hideyo\Ecommerce\Framework\Services\ExtraField\Entity\ExtraField;
use Hideyo\Ecommerce\Framework\Services\ExtraField\Entity\ExtraFieldDefaultValue;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;

class ExtraFieldRepository extends BaseRepository 
{
    protected $model;

    public function __construct(
        ExtraField $model, 
        ExtraFieldDefaultValue $modelValue)
    {
        $this->model = $model;
        $this->modelValue = $modelValue;
    }

    public function findValue($defaultValueId)
    {
        return $this->modelValue->find($defaultValueId);
    }

    public function getValueModel()
    {
        return $this->modelValue;
    }

    public function selectAllByAllProductsAndProductCategoryId($productCategoryId)
    {
        return $this->model->select('extra_field.*')
        ->leftJoin('product_category_related_extra_field', 'extra_field.id', '=', 'product_category_related_extra_field.extra_field_id')
        
        ->where(function ($query) use ($productCategoryId) {

            $query->where('all_products', '=', 1)
            ->orWhereHas('categories', function ($query) use ($productCategoryId) {

                $query->where('product_category_id', '=', $productCategoryId);
            });
        })

        ->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->get();
    }    
}