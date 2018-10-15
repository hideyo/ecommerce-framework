<?php

namespace Hideyo\Ecommerce\Framework\Services\GeneralSetting;

use Validator;
use Hideyo\Ecommerce\Framework\Services\GeneralSetting\Entity\GeneralSettingRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class GeneralSettingService extends BaseService
{
	public function __construct(GeneralSettingRepository $generalSetting)
	{
		$this->repo = $generalSetting;
	} 

 	/**
     * The validation rules for the model.
     *
     * @param  integer  $settingId id attribute model    
     * @return array
     */
    public function rules($settingId = false)
    {
        $rules = array(
            'name' => 'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id',
            'value' => 'required'
        );
        
        if ($settingId) {
            $rules['name'] =   $rules['name'].','.$settingId.' = id';
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
        return $this->updateOrAddModel($this->repo->getModel(), $attributes);
    }

    public function updateById(array $attributes, $id)
    {
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rules($id));
        if ($validator->fails()) {
            return $validator;
        }

        $model = $this->find($id);
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        return $this->updateOrAddModel($model, $attributes);   
    }  
}