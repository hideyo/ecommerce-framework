<?php

namespace Hideyo\Ecommerce\Framework\Services\Attribute;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\Attribute\Entity\AttributeRepository;
use Hideyo\Ecommerce\Framework\Services\Attribute\Entity\AttributeGroupRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class AttributeService extends BaseService
{
	public function __construct(AttributeRepository $attribute, AttributeGroupRepository $attributeGroup)
	{
		$this->repo = $attribute;
		$this->repoGroup = $attributeGroup;		
	} 

    private function rules($id = false)
    {
        $rules = array(
            'value' => 'required|unique_with:'.$this->repo->getModel()->getTable().',attribute_group_id'
        );

        if ($id) {
            $rules['value'] = $rules['value'].','.$id;
        }

        return $rules;
    }

    private function rulesGroup($id = false)
    {
        $rules = array(
            'title' => 'required|between:1,65|unique_with:'.$this->repoGroup->getModel()->getTable().', shop_id'
        );
        
        if ($id) {
            $rules['title'] =   $rules['title'].','.$id.' = id';
        }

        return $rules;
    }

    public function create(array $attributes, $attributeGroupId)
    {
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
  		$attributes['attribute_group_id'] = $attributeGroupId;

        $validator = Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }

        $this->repo->getModel()->fill($attributes);
        $this->repo->getModel()->save();
        return $this->repo->getModel();
    }


    public function createGroup(array $attributes)
    {
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        $validator = Validator::make($attributes, $this->rulesGroup());

        if ($validator->fails()) {
            return $validator;
        }

        $this->repoGroup->getModel()->fill($attributes);
        $this->repoGroup->getModel()->save();
        return $this->repoGroup->getModel();
    }

    public function updateById(array $attributes, $attributeGroupId, $id)
    {
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $attributes['attribute_group_id'] = $attributeGroupId;
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;

        $validator = Validator::make($attributes, $this->rules($id));
        
        if ($validator->fails()) {
            return $validator;
        }

        $model = $this->find($id);
        $model->fill($attributes);
        $model->save();

        return $model;    
    } 

    public function updateGroupById(array $attributes, $id)
    {
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rulesGroup($id));
        if ($validator->fails()) {
            return $validator;
        }

        $model = $this->findGroup($id);
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;

        $model->fill($attributes);
        $model->save();

        return $model;    
    } 

    public function selectAllGroups()
    {
        return $this->repoGroup->selectAll();
    }

    public function getGroupModel()
    {
        return $this->repoGroup->getModel();
    }

    public function findGroup($groupId)
    {
        return $this->repoGroup->find($groupId);
    }
}