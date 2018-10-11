<?php

namespace Hideyo\Ecommerce\Framework\Services\Product;
 
use App\Product;
use Validator;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductAmountSeriesRepository;
use Hideyo\Ecommerce\Framework\Services\Product\ProductFacade as ProductService;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ProductAmountSeriesService extends BaseService
{
	public function __construct(ProductAmountSeriesRepository $productAmountSeries)
	{
		$this->repo = $productAmountSeries;
	} 


 /**
     * The validation rules for the model.
     *
     * @param  integer  $id id attribute model    
     * @return array
     */
    private function rules($id = false)
    {
        $rules = array(
            'series_value' => 'required',
            'series_max' => 'required',
        );
        
        return $rules;
    }

    public function create(array $attributes, $productId)
    {
        $product = ProductService::find($productId);
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $attributes['product_id'] = $product->id;
        $validator = Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
            
        $this->repo->getModel()->fill($attributes);
        $this->repo->getModel()->save();
   
        return $this->repo->getModel();
    }

    public function updateById(array $attributes, $productId, $id)
    {
        $model = $this->find($id);
        $attributes['product_id'] = $productId;
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules($id));

        if ($validator->fails()) {
            return $validator;
        }
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;


        $model->fill($attributes);
        $model->save();

        return $model;
    }
}