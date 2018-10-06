<?php

namespace Hideyo\Ecommerce\Framework\Services\Product;
 
use App\Product;

use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductTagGroupRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ProductTagGroupService extends BaseService
{
	public function __construct(ProductTagGroupRepository $productTagGroup)
	{
		$this->repo = $productTagGroup;
	} 

	public function selectAllByTagAndShopId($shopId, $tag) {
		return $this->repo->selectAllByTagAndShopId($shopId, $tag);
	}

  /**
     * The validation rules for the model.
     *
     * @param  integer  $tagGroupId id attribute model    
     * @return array
     */
    private function rules($tagGroupId = false)
    {
        $rules = array(
            'tag' => 'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id'

        );
        
        if ($tagGroupId) {
            $rules['tag'] =   'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id, '.$tagGroupId.' = id';
        }

        return $rules;
    }

  
    public function create(array $attributes)
    {
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
            
        $this->repo->getModel()->fill($attributes);
        $this->repo->getModel()->save();

        if (isset($attributes['products'])) {
            $this->repo->getModel()->relatedProducts()->sync($attributes['products']);
        }
   
        return $this->repo->getModel();
    }

    public function updateById(array $attributes, $tagGroupId)
    {
        $model = $this->find($tagGroupId);
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules($tagGroupId));

        if ($validator->fails()) {
            return $validator;
        }
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;

        $model->fill($attributes);
        $model->save();

        return $model;
    }

}