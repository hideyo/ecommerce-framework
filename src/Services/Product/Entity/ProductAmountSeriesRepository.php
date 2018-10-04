<?php
namespace Hideyo\Ecommerce\Framework\Services\Product\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductAmountSeries;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class ProductAmountSeriesRepository extends BaseRepository
{

    protected $model;

    public function __construct(ProductAmountSeries $model, ProductRepository $product)
    {
        $this->model = $model;
        $this->product = $product;
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
        $product = $this->product->find($productId);
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
              $attributes['product_id'] = $product->id;
        $validator = \Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
            
        $this->model->fill($attributes);
        $this->model->save();
   
        return $this->model;
    }

    public function updateById(array $attributes, $productId, $id)
    {
        $this->model = $this->find($id);
                $attributes['product_id'] = $productId;
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = \Validator::make($attributes, $this->rules($id));

        if ($validator->fails()) {
            return $validator;
        }
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        return $this->updateEntity($attributes);
    }
}