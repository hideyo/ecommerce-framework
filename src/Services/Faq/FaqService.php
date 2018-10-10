<?php

namespace Hideyo\Ecommerce\Framework\Services\Faq;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\Faq\Entity\FaqItemRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class FaqService extends BaseService
{
	public function __construct(FaqItemRepository $faq)
	{
		$this->repo = $faq;
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
                'meta_title'                 => 'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id'
            );
        } else {
            $rules = array(
                'question'                 => 'required|between:4,65|unique:'.$this->repo->getModel()->getTable().''
            );
            
            if ($faqItemId) {
                $rules['question'] =   'required|between:4,65|unique:'.$this->repo->getModel()->getTable().',question,'.$faqItemId;
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
        return $this->updateOrAddModel($this->repo->getModel(), $attributes);
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
        return $this->updateOrAddModel($model, $attributes); 
    }  
    


    public function selectAllGroups()
    {
    	return $this->repo->selectAllGroups();
    
    }

}