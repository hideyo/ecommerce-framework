<?php

namespace Hideyo\Ecommerce\Framework\Services\Product;
 
use App\Product;

use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductExtraFieldValueRepository;
use Hideyo\Ecommerce\Framework\Services\Product\ProductFacade as ProductService;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ProductExtraFieldValueService extends BaseService
{
	public function __construct(ProductExtraFieldValueRepository $productExtraFieldValue)
	{
		$this->repo = $productExtraFieldValue;
	} 

    public function selectAllByProductId($productId)
    {
        return $this->repo->selectAllByProductId($productId);
    }

    public function selectAllByProductCategoryId($productCategoryId, $shopId)
    {
        return $this->repo->selectAllByProductCategoryId($productCategoryId, $shopId);
    }

    public function create(array $attributes, $productId)
    {
        $product = ProductService::find($productId);
        $remove  = $this->repo->getModel()->where('product_id', '=', $productId)->delete();
                
        $result = false;
        if (isset($attributes['rows'])) {
            foreach ($attributes['rows'] as $row) {
                $data = array();

                $check  = $this->repo->getModel()->where('extra_field_id', '=', $row['extra_field_id'])->where('product_id', '=', $productId)->first();
                $data['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
                if (!empty($row['extra_field_default_value_id']) or !empty($row['value'])) {
                    if ($check) {
                        $data['modified_by_user_id'] = auth('hideyobackend')->user()->id;
                        $data['extra_field_id'] = $row['extra_field_id'];
                        $data['product_id'] = $product->id;
                        if (isset($row['extra_field_default_value_id']) and $row['extra_field_default_value_id']) {
                            $data['extra_field_default_value_id'] = $row['extra_field_default_value_id'];
                        } else {
                            $data['extra_field_default_value_id'] = null;
                        }
               
                        $data['value'] = $row['value'];

                        $result = $this->repo->getModel()->find($check->id);
                        $result->fill($data);
                        $result->save();
                    } else {
                        $data['modified_by_user_id'] = auth('hideyobackend')->user()->id;
                        $data['extra_field_id'] = $row['extra_field_id'];
                        $data['product_id'] = $product->id;
                        if (isset($row['extra_field_default_value_id']) and $row['extra_field_default_value_id']) {
                            $data['extra_field_default_value_id'] = $row['extra_field_default_value_id'];
                        }
                        $data['value'] = $row['value'];

                        $result = $this->repo->getModel();
                        $result->fill($data);
                        $result->save();
                    }
                }
            }
        }

        return $result;
    }


}