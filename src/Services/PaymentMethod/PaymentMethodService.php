<?php

namespace Hideyo\Ecommerce\Framework\Services\PaymentMethod;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\PaymentMethod\Entity\PaymentMethodRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class PaymentMethodService extends BaseService
{
	public function __construct(PaymentMethodRepository $paymentMethod)
	{
		$this->repo = $paymentMethod;
	}

    public function selectOneByShopIdAndId($shopId, $paymentMethodId)
    {
    	$result = $this->repo->selectOneByShopIdAndId($shopId, $paymentMethodId);

        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();

    }

    /**
     * The validation rules for the model.
     *
     * @param  integer  $paymentMethodId id attribute model    
     * @return array
     */
    private function rules($paymentMethodId = false)
    {
        $rules = array(
            'title' => 'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id',
            'price'  => 'numeric|required'
        );
        
        if ($paymentMethodId) {
            $rules['title'] =   $rules['title'].','.$paymentMethodId.' = id';
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

    public function updateById(array $attributes, $paymentMethodId)
    {
        $model = $this->find($paymentMethodId);
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;        
        $validator = Validator::make($attributes, $this->rules($paymentMethodId));

        if ($validator->fails()) {
            return $validator;
        }

        if($model) {

            $model->fill($attributes);
            $model->save();
            return $model;
        }

        return false;
    }


}