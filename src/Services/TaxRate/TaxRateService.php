<?php

namespace Hideyo\Ecommerce\Framework\Services\TaxRate;

use Validator;
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
            'title' => 'required|between:2,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id',
            'rate'  => 'numeric|required'
        );
        
        if($taxRateId) {
            $rules['title'] =   $rules['title'].','.$taxRateId.' = id';
        }

        return $rules;
    } 

    /**
     * create model
     * @param  array  $attributes 
     * @return mixed         
     */
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

    /**
     * update model by id
     * @param  array  $attributes 
     * @param  integer $taxRateId         
     * @return mixed            
     */
    public function updateById(array $attributes, $taxRateId)
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