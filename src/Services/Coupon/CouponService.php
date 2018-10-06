<?php

namespace Hideyo\Ecommerce\Framework\Services\Coupon;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\Coupon\Entity\CouponRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class CouponService extends BaseService
{
	public function __construct(CouponRepository $coupon)
	{
		$this->repo = $coupon;
	}


    /**
     * The validation rules for the model.
     *
     * @param  integer  $couponId id attribute model    
     * @return array
     */
    private function rules($couponId = false)
    {
        $rules = array(
            'title' => 'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id',
            'code' => 'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id',
            'product_id' => 'integer',
            'product_category_id' => 'integer'
        );
        
        if ($couponId) {
            $rules['title'] =   'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id, '.$couponId.' = id';
            $rules['code'] =   'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id, '.$couponId.' = id';
        }

        return $rules;
    }

    private function rulesGroup($groupId = false, $attributes = false)
    {

        $rules = array(
            'title'                 => 'required|between:4,65|unique_with:'.$this->repo->getGroupModel()->getTable().', shop_id'
        );
        
        if ($groupId) {
            $rules['title'] =   'required|between:4,65|unique_with:'.$this->repo->getGroupModel()->getTable().', shop_id, '.$groupId.' = id';
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
        
        if (isset($attributes['product_categories'])) {
            $this->repo->getModel()->productCategories()->sync($attributes['product_categories']);
        }

        if (isset($attributes['products'])) {
            $this->repo->getModel()->products()->sync($attributes['products']);
        }

        if (isset($attributes['sending_methods'])) {
            $this->repo->getModel()->sendingMethods()->sync($attributes['sending_methods']);
        }

        if (isset($attributes['payment_methods'])) {
            $this->repo->getModel()->paymentMethods()->sync($attributes['payment_methods']);
        }

        return $this->repo->getModel();
    }

  
    public function createGroup(array $attributes)
    {
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rulesGroup());

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
            
        $this->modelGroup->fill($attributes);
        $this->modelGroup->save();
   
        return $this->modelGroup;
    }


    public function updateById(array $attributes, $couponId)
    {
        $model = $this->find($couponId);
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;

        $validator = Validator::make($attributes, $this->rules($couponId));

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;


        if (count($attributes) > 0) {
            $model->fill($attributes);

            $model->productCategories()->sync(array());
            $model->products()->sync(array());
            $model->sendingMethods()->sync(array());
            $model->paymentMethods()->sync(array());

            if (isset($attributes['product_categories'])) {
                $model->productCategories()->sync($attributes['product_categories']);
            }

            if (isset($attributes['products'])) {
                $model->products()->sync($attributes['products']);
            }

            if (isset($attributes['sending_methods'])) {
                $model->sendingMethods()->sync($attributes['sending_methods']);
            }

            if (isset($attributes['payment_methods'])) {
                $model->paymentMethods()->sync($attributes['payment_methods']);
            }

            $model->save();
        }

        return $model;

    }


    public function updateGroupById(array $attributes, $groupId)
    {
        $validator = Validator::make($attributes, $this->rulesGroup($groupId, $attributes));

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        $modelGroup = $this->findGroup($groupId);

        if (count($attributes) > 0) {
            $modelGroup->fill($attributes);
            $modelGroup->save();
        }

        return $modelGroup;

    }

    public function destroyGroup($groupId)
    {
        $this->modelGroup = $this->findGroup($groupId);
        $this->modelGroup->save();

        return $this->modelGroup->delete();
    }

    public function getGroupModel()
    {
        return $this->repo->getGroupModel();
    } 

    public function findGroup($groupId)
    {
        return $this->repo->findGroup($groupId);
    } 


    public function selectAllGroups()
    {
        return $this->repo->selectAllGroups();
    }

}