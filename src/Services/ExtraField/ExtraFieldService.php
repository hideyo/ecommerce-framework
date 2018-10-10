<?php

namespace Hideyo\Ecommerce\Framework\Services\ExtraField;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\ExtraField\Entity\ExtraFieldRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ExtraFieldService extends BaseService
{
	public function __construct(ExtraFieldRepository $extraField)
	{
		$this->repo = $extraField;
	}



    private function rulesValue($defaultValueId = false)
    {
        if ($defaultValueId) {
            return [
                'value' => 'required|unique_with:'.$this->getValueModel()->getModel()->getTable().',extra_field_id,'.$defaultValueId,
            ];
        } else {
            return [
                'value' => 'required|unique_with:'.$this->getValueModel()->getModel()->getTable().',extra_field_id'
            ];
        }
    }




    /**
     * The validation rules for the model.
     *
     * @param  integer  $extraFieldId id attribute model    
     * @return array
     */
    private function rules($extraFieldId = false)
    {
        $rules = array(
            'title' => 'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id'
        );
        
        if ($extraFieldId) {
            $rules['title'] =   'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id, '.$extraFieldId.' = id';
        }

        return $rules;
    }


    public function create(array $attributes)
    {
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        $this->repo->getModel()->fill($attributes);
        $this->repo->getModel()->save();

        if (isset($attributes['categories'])) {
            $this->repo->getModel()->categories()->sync($attributes['categories']);
        }
        
        return $this->repo->getModel();
    }


    public function updateById(array $attributes, $extraFieldId)
    {
        $model = $this->find($extraFieldId);
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rules($extraFieldId));

        if ($validator->fails()) {
            return $validator;
        }
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;

        if (count($attributes) > 0) {
            $model->fill($attributes);
            
            $model->categories()->sync(array());

            if (isset($attributes['categories'])) {
                $model->categories()->sync($attributes['categories']);
            }

            $model->save();
        }

        return $model;



    }


    public function createValue(array $attributes, $extraFieldId)
    {
        $attributes['extra_field_id'] = $extraFieldId;
        $validator = Validator::make($attributes, $this->rulesValue());

        if ($validator->fails()) {
            return $validator;
        } else {
            $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
            $this->getValueModel()->getModel()->fill($attributes);
            $this->getValueModel()->getModel()->save();
            return $this->getValueModel()->getModel();
        }
    }

    public function updateValueById(array $attributes, $extraFieldId, $defaultValueId)
    {
        $attributes['extra_field_id'] = $extraFieldId;
        $validator = Validator::make($attributes, $this->rulesValue($defaultValueId));

        if ($validator->fails()) {
            return $validator;
        }

        $modelValue = $this->findValue($defaultValueId);
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;

        if (count($attributes) > 0) {
            $modelValue->fill($attributes);

            $modelValue->save();
        }

        return $modelValue;


    }

    public function selectAllByAllProductsAndProductCategoryId($productCategoryId)
    {
        return $this->repo->selectAllByAllProductsAndProductCategoryId($productCategoryId);
    }


    public function destroyValue($defaultValueId)
    {
        $this->modelValue = $this->findValue($defaultValueId);
        $this->modelValue->save();

        return $this->modelValue->delete();
    }


    public function findValue($id)
    {
        return $this->repo->findValue($id);
    }


    public function getValueModel()
    {
        return $this->repo->getValueModel();
    }




}