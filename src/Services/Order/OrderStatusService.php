<?php

namespace Hideyo\Ecommerce\Framework\Services\Order;
 
use Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderStatus;

use Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderStatusRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
use Validator;

class OrderStatusService extends BaseService
{
	public function __construct(OrderStatusRepository $orderStatus)
	{
		$this->repo = $orderStatus;
	} 

    /**
     * The validation rules for the model.
     *
     * @param  integer  $id id attribute model    
     * @return array
     */  
    public function rules($id = false)
    {
        $rules = array(
            'title' => 'required|between:4,65|unique_with:order_status, shop_id'

        );
        
        if ($id) {
            $rules['title'] =   'required|between:4,65|unique_with:order_status, shop_id, '.$id.' = id';
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
        return $this->repo->getModel();
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
        $model->fill($attributes);
        $model->save();

        return $model;
    }  


}