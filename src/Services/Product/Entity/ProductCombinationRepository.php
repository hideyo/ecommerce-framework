<?php
namespace Hideyo\Ecommerce\Framework\Services\Product\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductAttribute;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductAttributeCombination;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductRepository;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class ProductCombinationRepository extends BaseRepository 
{

    protected $model;

    public function __construct(ProductAttribute $model, ProductAttributeCombination $modelAttributeCombination, ProductRepository $product)
    {
        $this->model = $model;
        $this->modelAttributeCombination = $modelAttributeCombination;
        $this->product = $product;
    }
  
 
    public function getModelAttributeCombination() {
        return $this->modelAttributeCombination;
    }

    public function selectAllByProductId($productId)
    {
        return $this->model->where('product_id', '=', $productId)->get();
    }

    public function selectAllByShopIdAndProductId($shopId, $productId)
    {
        return $this->model->select('id')->where('product_id', '=', $productId)->with(array('combinations' => function ($query) {
            $query->with(array('attribute' => function ($query) {
                $query->with(array('attributeGroup'));
            }));
        }))->get();
    }

    function selectOneByShopIdAndSlug($shopId, $slug)
    {
           return $this->model->with(array('productCategory', 'productImages'))->get()->first();
    }

    function selectOneByShopIdAndId($shopId, $productAttributeId)
    {
           return $this->model->with(array('productCategory', 'productImages'))->where('id', '=', $productAttributeId)->get()->first();
    }
    

    public function changeAmount($productAttributeId, $amount)
    {
        $this->model = $this->find($productAttributeId);

        if ($this->model) {
            $attributes = array(
                'amount' => $amount
            );

            $this->model->fill($attributes);

            return $this->model->save();
        }

        return false;
    }

    public function selectAllByProductCategoryId($productCategoryId, $shopId)
    {
         return $this->model->
         whereHas('product', function ($query) use ($productCategoryId, $shopId) {
            $query->where('product_category_id', '=', $productCategoryId);
            $query->where('active', '=', 1);
            $query->where('shop_id', '=', $shopId);
         })->with(array('combinations' => function ($q) {
            $q->with(array('attribute' => function ($q) {
                $q->with(array('attributeGroup'));
            }));
         }))->get();
    }

    public function increaseAmounts($products)
    {
        if ($products->count()) {
            foreach ($products as $product) {
                if ($product->product_attribute_id) {
                    $this->model = $this->find($product->product_attribute_id);
                    $attributes = array(
                        'amount' => $this->model->amount + $product->amount
                    );

                    $this->model->fill($attributes);
                    $this->model->save();
                }
            }
        }
    }

    public function reduceAmounts($products)
    {
        if ($products->count()) {
            foreach ($products as $product) {
                if ($product->product_attribute_id) {
                    $this->model = $this->find($product->product_attribute_id);
                    $attributes = array(
                        'amount' => $this->model->amount - $product->amount
                    );

                    $this->model->fill($attributes);
                    $this->model->save();
                }
            }
        }
    }

    function getProductAttribute($product, $productAttributeId, $secondAttributeId = false) {   
       $productAttribute = $this->model->where('product_id', '=', $product->id)
        ->whereHas('combinations', function ($query) use ($productAttributeId, $secondAttributeId) {
            if ($productAttributeId) {
                $query->where('attribute_id', '=', $productAttributeId);
            }
        })
        ->whereHas('combinations', function ($query) use ($secondAttributeId) {
            if ($secondAttributeId) {
                $query->where('attribute_id', '=', $secondAttributeId);
            }
        })
        ->with(array('combinations' => function ($query) {
            $query->with(array('attribute' => function ($query) {
                $query->with(array('attributeGroup'));
            }));
        }))        
        ->with(array('product'));

        return $productAttribute;
    }
}