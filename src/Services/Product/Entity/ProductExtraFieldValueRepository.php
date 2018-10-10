<?php
namespace Hideyo\Ecommerce\Framework\Services\Product\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductExtraFieldValue;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductRepository;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class ProductExtraFieldValueRepository extends BaseRepository 
{

    protected $model;

    public function __construct(ProductExtraFieldValue $model, ProductRepository $product)
    {
        $this->model = $model;
        $this->product = $product;
    }
  
 
    public function selectAllByProductId($productId)
    {
        return $this->model->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->where('product_id', '=', $productId)->get();
    }

    public function selectByProductIdAndExtraFieldId($productId, $extraFieldId)
    {

        return $this->model->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->where('product_id', '=', $productId)->where('extra_field_id', '=', $extraFieldId)->get();
    }

    function selectOneByShopIdAndSlug($shopId, $slug)
    {
           return $this->model->with(array('productCategory', 'productImages'))->where('shop_id', '=', $shopId)->where('slug', '=', $slug)->get()->first();
    }

    function selectOneByShopIdAndId($shopId, $id)
    {
           return $this->model->with(array('productCategory', 'productImages'))->where('shop_id', '=', $shopId)->where('id', '=', $id)->get()->first();
    }

    function selectAllByProductCategoryId($productCategoryId, $shopId)
    {

         return $this->model->
         whereHas('product', function ($query) use ($productCategoryId, $shopId) {
            $query->where('product_category_id', '=', $productCategoryId);
            $query->where('active', '=', 1);
            $query->where('shop_id', '=', $shopId);
         })->with(
             array(
                'extraFieldDefaultValue',
                'extraField' => function ($q) {
                }
                )
         )->get();
    }
}