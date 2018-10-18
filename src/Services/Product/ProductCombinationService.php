<?php

namespace Hideyo\Ecommerce\Framework\Services\Product;
 
use App\Product;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductAttributeCombination;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductCombinationRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ProductCombinationService extends BaseService
{
	public function __construct(ProductCombinationRepository $productTagGroup)
	{
		$this->repo = $productTagGroup;
	} 

   public function create(array $attributes, $productId)
{
        if (isset($attributes['selected_attribute_ids'])) {
            $check = $this->repo->getModelAttributeCombination()->leftJoin($this->repo->getModel()->getTable(), $this->repo->getModel()->getTable().'.id', '=', $this->repo->getModelAttributeCombination()->getTable().'.product_attribute_id')
            ->where($this->repo->getModel()->getTable().'.product_id', '=', $productId);

            if (isset($attributes['selected_attribute_ids'])) {
                $check->where(function ($query) use ($attributes) {
                    $query->whereIn($this->repo->getModelAttributeCombination()->getTable().'.attribute_id', $attributes['selected_attribute_ids']);
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
            $data['product_id'] = $productId;

            $new = $this->repo->getModel();
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
        $model = $this->find($productAttributeId);

        if (count($attributes) > 0) {
            $model->fill($attributes);
            $model->save();

            $model->combinations()->delete();


            $check = $this->repo->getModelAttributeCombination()->leftJoin($this->repo->getModel()->getTable(), $this->repo->getModel()->getTable().'.id', '=', $this->repo->getModelAttributeCombination()->getTable().'.product_attribute_id')
            ->where($this->repo->getModel()->getTable().'.product_id', '=', $attributes['product_id']);

            if (isset($attributes['selected_attribute_ids'])) {
                $check->where(function ($query) use ($attributes) {
                    $query->whereIn($this->repo->getModelAttributeCombination()->getTable().'.attribute_id', $attributes['selected_attribute_ids']);
                });
            }

            if ($check->get()->count()) {
                $newData = array();

                foreach ($check->get() as $row) {
                    $newData[$row['product_attribute_id']] = $row->productAttribute->combinations->toArray();
                }

                if(isset($attributes['selected_attribute_ids'])) {
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
            }


            if (isset($attributes['selected_attribute_ids'])) {


                foreach ($attributes['selected_attribute_ids'] as $row) {
                    $newData = array(
                        'attribute_id' => $row,
                        'product_attribute_id' => $model->id,

                    );

                    $ok = new ProductAttributeCombination;
                    $ok->fill($newData);
                    $ok->save();
                    $ok->refresh();
                }
            }
        }

        return $model;
    }


    public function increaseAmounts($products)
    {
        if ($products->count()) {
            foreach ($products as $product) {
                if ($product->product_attribute_id) {
                    $model = $this->find($product->product_attribute_id);
                    $attributes = array(
                        'amount' => $model->amount + $product->amount
                    );

                    $model->fill($attributes);
                    $model->save();
                }
            }
        }
    }

    public function reduceAmounts($products)
    {
        if ($products->count()) {
            foreach ($products as $product) {
                if ($product->product_attribute_id) {
                    $model = $this->find($product->product_attribute_id);
                    $attributes = array(
                        'amount' => $model->amount - $product->amount
                    );

                    $model->fill($attributes);
                    $model->save();
                }
            }
        }
    }


	public function selectAllByProductCategoryId($productCategoryId, $shopId) {
		return $this->repo->selectAllByProductCategoryId($productCategoryId, $shopId);
	}

    public function generatePulldowns($product, $productAttributeId, $attributeLeadingGroup = false, $secondAttributeId = false) 
    {
        $defaultOption = array();
        $check = array();

        //create all pulldowns
        foreach ($product->attributes as $row) {
            if ($row['combinations']) {
                foreach ($row['combinations'] as $key => $value) {
                    $newPullDowns[$value->attribute->attributeGroup->title][$value->attribute->id] = $value->attribute->value;
                }
            }
        }

        if(!$productAttributeId AND $attributeLeadingGroup AND isset($newPullDowns[$attributeLeadingGroup->title])) {
            $productAttributeId = key($newPullDowns[$attributeLeadingGroup->title]);
        }

        $productAttributeResultWithAttributeId =  $this->repo->getProductAttribute($product, $productAttributeId);   

        if ($productAttributeResultWithAttributeId->get()->first()) {
            foreach ($productAttributeResultWithAttributeId->get()->first()->combinations as $combination) {
                $defaultOption[$combination->attribute->attributeGroup->title][$combination->attribute->id] = $combination->attribute->value;
            }
        } else {
            $productAttributeId = false;
        }

        $productAttributeResultWithAttributeId = $productAttributeResultWithAttributeId->get();

        if ($productAttributeResultWithAttributeId) {

            foreach ($productAttributeResultWithAttributeId as $row) {
                if ($row['combinations']) {
                    foreach ($row['combinations'] as $key => $value) {
                        $defaultOption[$value->attribute->attributeGroup->title][$value->attribute->id] = $value->attribute->value;
                    }
                }
            }
        }

        $defaultPulldown = array();
        if ($attributeLeadingGroup AND isset($newPullDowns[$attributeLeadingGroup->title])) {
            $defaultOption[$attributeLeadingGroup->title] = $newPullDowns[$attributeLeadingGroup->title];
            $newPullDowns = $defaultOption;
        }

        if ($attributeLeadingGroup AND isset($defaultOption[$attributeLeadingGroup->title])) {
            $defaultPulldown = $newPullDowns[$attributeLeadingGroup->title];
            $defaultPulldownFirstKey = key($newPullDowns[$attributeLeadingGroup->title]);
            unset($newPullDowns[$attributeLeadingGroup->title]);
            $newPullDowns = array_merge(array($attributeLeadingGroup->title => $defaultPulldown), $newPullDowns);
        }

        $productAttribute = $this->repo->getProductAttribute($product, $productAttributeId, $secondAttributeId)->first();

        return array('productAttribute' => $productAttribute, 'productAttributeId' => $productAttributeId, 'defaultOption' => $defaultOption, 'newPullDowns' => $newPullDowns);
    }


}