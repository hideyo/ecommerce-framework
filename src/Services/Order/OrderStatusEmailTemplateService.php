<?php

namespace Hideyo\Ecommerce\Framework\Services\Order;
 
use Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderStatusEmailTemplate;

use Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderStatusEmailTemplateRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
use Validator;

class OrderStatusEmailTemplateService extends BaseService
{
	public function __construct(OrderStatusEmailTemplateRepository $orderStatusEmailTemplate)
	{
		$this->repo = $orderStatusEmailTemplate;
	} 




    /**
     * The validation rules for the model.
     *
     * @param  integer  $id id attribute model    
     * @return array
     */
    private function rules($id = false, $attributes = false)
    {
        $rules = array(
            'title' => 'required|unique_with:order_status_email_template, shop_id',
            'subject' => 'required',
            'content' => 'required'
        );
        
        if ($id) {
            $rules['title'] =   'required|unique_with:order_status_email_template, shop_id,'.$id;
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

        $this->repo->getModel()->fill($attributes);
 
        $this->repo->getModel()->save();
        
        return $this->repo->getModel();
    }

    public function selectAllByShopId($shopId) {
        return $this->repo->selectAllByShopId($shopId);
    }

    public function updateById(array $attributes, $id)
    {
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules($id, $attributes));

        if ($validator->fails()) {
            return $validator;
        }

       
        $model = $this->find($id);

        $model->fill($attributes);
 
        $model->save();

        return $model;

    }

}