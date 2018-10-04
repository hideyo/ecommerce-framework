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
  
    public function create(array $attributes, $productId)
    {
        $product = $this->product->find($productId);

        if (isset($attributes['selected_attribute_ids'])) {
            $check = ProductAttributeCombination::leftJoin($this->model->getTable(), $this->model->getTable().'.id', '=', $this->modelAttributeCombination->getTable().'.product_attribute_id')
            ->where($this->model->getTable().'.product_id', '=', $productId);

            if (isset($attributes['selected_attribute_ids'])) {
                $check->where(function ($query) use ($attributes) {
                    $query->whereIn($this->modelAttributeCombination->getTable().'.attribute_id', $attributes['selected_attribute_ids']);
                });
            }

            if ($check->get()->count()) {
                $newData = array();
                foreach ($check->get() as $row) {
                    $newData[$row['product_attribute_id']] = $row->productAttribute->combinations->toArray();
                }

                foreach ($newData as $row) {
                    if (count($row) == count($attributes['selected_attribute_ids'])) {
                        $i = 0;
                        foreach ($row as $newRow) {
                            if (in_array($newRow['attribute_id'], $attributes['selected_attribute_ids'])) {
                                $i++;
                            }
                        }
                        
                        if (count($row) == $i) {
                            return false;
                        }
                    }
                }
            }

            $data = $attributes;
            $data['modified_by_user_id'] = auth('hideyobackend')->user()->id;
            $data['product_id'] = $product->id;

            $new = new ProductAttribute;
            $new->fill($data);
            $new->save();

            if (isset($attributes['selected_attribute_ids'])) {
                foreach ($attributes['selected_attribute_ids'] as $row) {
                    $newData = array(
                        'attribute_id' => $row,
                        'product_attribute_id' => $new->id,

                    );

                    $ok = new ProductAttributeCombination;
                    $ok->fill($newData);
                    $ok->save();
                }
            }

            return $new;
        }
    }

    public function updateById(array $attributes, $productId, $productAttributeId)
    {
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        $attributes['product_id'] = $productId;
        $this->model = $this->find($productAttributeId);
        return $this->updateEntity($attributes);
    }

    public function updateEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->model->fill($attributes);
            $this->model->save();

            $this->model->combinations()->delete();


            $check = ProductAttributeCombination::leftJoin($this->model->getTable(), $this->model->getTable().'.id', '=', $this->modelAttributeCombination->getTable().'.product_attribute_id')
            ->where($this->model->getTable().'.product_id', '=', $attributes['product_id']);

            if (isset($attributes['selected_attribute_ids'])) {
                $check->where(function ($query) use ($attributes) {
                    $query->whereIn($this->modelAttributeCombination->getTable().'.attribute_id', $attributes['selected_attribute_ids']);
                });
            }

            if ($check->get()->count()) {
                $newData = array();
                foreach ($check->get() as $row) {
                    $newData[$row['product_attribute_id']] = $row->productAttribute->combinations->toArray();
                }

                foreach ($newData as $row) {
                    if (count($row) == count($attributes['selected_attribute_ids'])) {
                        $i = 0;
                        foreach ($row as $newRow) {
                            if (in_array($newRow['attribute_id'], $attributes['selected_attribute_ids'])) {
                                $i++;
                            }
                        }
                        
                        if (count($row) == $i) {
                            return false;
                        }
                    }
                }
            }


            if (isset($attributes['selected_attribute_ids'])) {
                foreach ($attributes['selected_attribute_ids'] as $row) {
                    $newData = array(
                        'attribute_id' => $row,
                        'product_attribute_id' => $this->model->id,

                    );

                    $ok = new ProductAttributeCombination;
                    $ok->fill($newData);
                    $ok->save();
                }
            }
        }

        return $this->model;
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