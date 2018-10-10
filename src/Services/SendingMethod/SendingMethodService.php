<?php

namespace Hideyo\Ecommerce\Framework\Services\SendingMethod;

use Validator;
use Hideyo\Ecommerce\Framework\Services\SendingMethod\Entity\SendingMethodRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class SendingMethodService extends BaseService
{
	public function __construct(SendingMethodRepository $sendingMethod)
	{
		$this->repo = $sendingMethod;
	} 

    /**
     * The validation rules for the model.
     *
     * @param  integer  $sendingMethodId id attribute model    
     * @return array
     */
    private function rules($sendingMethodId = false)
    {
        $rules = array(
            'active'            => 'required|integer',
            'title'             => 'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id',
            'price'             => 'numeric|required',
            'minimal_weight'    => 'numeric|nullable',
            'maximal_weight'    => 'numeric|nullable'
        );
        
        if($sendingMethodId) {
            $rules['title'] =   $rules['title'].','.$sendingMethodId.' = id';
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
        $model = $this->updateOrAddModel($this->repo->getModel(), $attributes);     


        if (isset($attributes['payment_methods'])) {
            $model->relatedPaymentMethods()->sync($attributes['payment_methods']);
        }
   
        return $model;
    }

    public function updateById(array $attributes, $sendingMethodId)
    {
        $model = $this->find($sendingMethodId);
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        $validator = Validator::make($attributes, $this->rules($sendingMethodId));
       
        if ($validator->fails()) {
            return $validator;
        }

        $model = $this->updateOrAddModel($model, $attributes); 
        $model->relatedPaymentMethods()->sync(array());
        if (isset($attributes['payment_methods'])) {
            $model->relatedPaymentMethods()->sync($attributes['payment_methods']);
        }

        return $model;
    }

	public function selectAllActiveByShopId($shopId)
    {
    	return $this->repo->selectAllActiveByShopId($shopId);
    }

	public function selectOneByShopIdAndId($shopId, $sendingMethodId)
    {
    	$result = $this->repo->selectOneByShopIdAndId($shopId, $sendingMethodId);

        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();	
    }
}