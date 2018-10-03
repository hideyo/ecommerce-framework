<?php
namespace Hideyo\Ecommerce\Framework\Repositories;
 
use Hideyo\Ecommerce\Framework\Models\FaqItem;
use Hideyo\Ecommerce\Framework\Models\FaqItemGroup;
 
class FaqItemRepository extends BaseRepository 
{

    protected $model;

    public function __construct(FaqItem $model, FaqItemGroup $modelFaqItemGroup)
    {
        $this->model = $model;
        $this->modelFaqItemGroup = $modelFaqItemGroup;
    }

    /**
     * The validation rules for the model.
     *
     * @param  integer  $faqItemId id attribute model    
     * @return array
     */
    private function rules($faqItemId = false, $attributes = false)
    {
        if (isset($attributes['seo'])) {
            $rules = array(
                'meta_title'                 => 'required|between:4,65|unique_with:'.$this->model->getTable().', shop_id'
            );
        } else {
            $rules = array(
                'question'                 => 'required|between:4,65|unique:'.$this->model->getTable().''
            );
            
            if ($faqItemId) {
                $rules['question'] =   'required|between:4,65|unique:'.$this->model->getTable().',question,'.$faqItemId;
            }
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
    

    public function selectAllGroups()
    {
        return $this->modelFaqItemGroup->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->get();
    }

    function selectOneById($faqItemId)
    {
        $result = $this->model->with(array('relatedPaymentMethods'))->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->where('active', '=', 1)->where('id', '=', $faqItemId)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }

    function selectAllActiveByShopId($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->get();
    }
        
}
