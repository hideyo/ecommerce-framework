<?php
namespace Hideyo\Ecommerce\Framework\Repositories;

use Hideyo\Ecommerce\Framework\Models\Redirect;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use File;
use Image;
use Validator;

class RedirectRepository  extends BaseRepository 
{

    protected $model;

    public function __construct(Redirect $model)
    {
        $this->model = $model;
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
            'url' => 'required|unique_with:'.$this->model->getTable().', shop_id'
        );
        
        if ($redirectId) {
            $rules['url'] = 'required|unique_with:'.$this->model->getTable().', shop_id, '.$redirectId.' = id';
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
    

    public function importCsv($results, $shopId)
    {
        foreach ($results as $row) {

            $attributes = $row->toArray();
            $attributes['shop_id'] = $shopId;
            $attributes['active'] = 0;
     
            $validator = Validator::make($attributes, $this->rules());

            if ($validator->fails()) {
    
                $result = $this->model->where('url', '=', $attributes['url'])->get()->first();
                if ($result) {
                    $attributes['active'] = 0;
                    if($attributes['redirect_url']) {
                        $attributes['active'] = 1;
                    } 
                    $this->model = $this->find($result->id);
                    $this->updateEntity($attributes);
                }

            } else {
                $redirect = new Redirect;
                $redirect->fill($attributes);
                $redirect->save();
         
            }
        }

        return true;
    }


    public function updateClicks($url)
    {
        $result = $this->model->where('url', '=', $url)->get()->first();
        if ($result) {
            $this->model = $this->find($result->id);
            return $this->updateEntity(array('clicks' => $result->clicks + 1));
        }
    }

    public function destroyByUrl($url)
    {
        $result = $this->model->where('url', '=', $url)->delete();
        return $result;
    }

    public function selectNewRedirects()
    {
        return \DB::table('number')
        ->leftJoin('user_number', 'number.id', '=', 'user_number.number_id')
        ->whereNull('user_number.number_id')
        ->select('number.id', 'number.number')
        ->get();
    }

    public function checkByCompanyIdAndUrl($companyId, $shopUrl)
    {
        $result = $this->model->where('company_id', '=', $companyId)->where('url', '=', $shopUrl)->get()->first();

        if (isset($result->id)) {
            return $result->id;
        }
        
        return false;
    }

    public function findByUrlAndActive($url)
    {
        $result = $this->model->where('url', '=', $url)->whereNotNull('redirect_url')->where('active', '=', 1)->get()->first();
        return $result;
    }

    public function findByUrl($url)
    {
        $result = $this->model->where('url', '=', $url)->get()->first();
        return $result;
    }
    
    public function findByCompanyIdAndUrl($companyId, $shopUrl)
    {
        $result = $this->model->where('company_id', '=', $companyId)->where('url', '=', $shopUrl)->get()->first();
        return $result;
    }    
}