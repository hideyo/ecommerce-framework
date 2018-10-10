<?php

namespace Hideyo\Ecommerce\Framework\Services\Redirect;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\Redirect\Entity\RedirectRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class RedirectService extends BaseService
{
	public function __construct(RedirectRepository $taxRate)
	{
		$this->repo = $taxRate;
	} 

    /**
     * The validation rules for the model.
     *
     * @param  integer  $redirectId id attribute model    
     * @return array
     */
    public function rules($redirectId = false)
    {
        $rules = array(
            'url' => 'required|unique_with:'.$this->repo->getModel()->getTable().', shop_id'
        );
        
        if ($redirectId) {
            $rules['url'] = 'required|unique_with:'.$this->repo->getModel()->getTable().', shop_id, '.$redirectId.' = id';
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

        if (count($attributes) > 0) {
            $model->fill($attributes);
            $model->save();
        }
        return $model;  
    } 

    public function importCsv($results, $shopId)
    {
        foreach ($results as $row) {

            $attributes = $row->toArray();
            $attributes['shop_id'] = $shopId;
            $attributes['active'] = 0;
     
            $validator = Validator::make($attributes, $this->rules());

            if ($validator->fails()) {
    
                $result = $this->repo->getModel()->where('url', '=', $attributes['url'])->get()->first();
                if ($result) {
                    $attributes['active'] = 0;
                    if($attributes['redirect_url']) {
                        $attributes['active'] = 1;
                    } 
                    $model = $this->find($result->id);
		            $model->fill($attributes);
		            $model->save();

		            return $model;
                }

            } else {
                $redirect = new Redirect;
                $redirect->fill($attributes);
                $redirect->save();
         
            }
        }

        return true;
    }	
}