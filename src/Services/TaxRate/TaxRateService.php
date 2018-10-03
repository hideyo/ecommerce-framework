<?php

namespace Hideyo\Ecommerce\Framework\Services\TaxRate;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\TaxRate\Entity\TaxRateRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class TaxRateService extends BaseService
{
	public function __construct(TaxRateRepository $taxRate)
	{
		$this->repo = $taxRate;
	} 


    /**
     * The validation rules for the model.
     *
     * @param  integer  $taxRateId id attribute model    
     * @return array
     */
    public function rules($taxRateId = false)
    {
        $rules = array(
            'title' => 'required|between:2,65|unique_with:'.$this->model->getTable().', shop_id',
            'rate'  => 'numeric|required'
        );
        
        if($taxRateId) {
            $rules['title'] =   $rules['title'].','.$taxRateId.' = id';
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
        $this->model->fill($attributes);
        $this->model->save();
        return $this->model;
    }

    public function updateById(array $attributes, $id)
    {
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rules($id));
        if ($validator->fails()) {
            return $validator;
        }

        $this->model = $this->find($id);
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        return $this->updateEntity($attributes);   
    } 
	
}