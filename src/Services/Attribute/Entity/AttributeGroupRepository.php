<?php
namespace Hideyo\Ecommerce\Framework\Services\Attribute\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Attribute\Entity\AttributeGroup;
use Validator;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class AttributeGroupRepository extends BaseRepository
{
    protected $model;

    public function __construct(AttributeGroup $model)
    {
        $this->model = $model;
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
            'title' => 'required|between:1,65|unique_with:'.$this->model->getTable().', shop_id'
        );
        
        if ($id) {
            $rules['title'] =   'required|between:1,65|unique_with:'.$this->model->getTable().', shop_id, '.$id.' = id';
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