<?php

namespace Hideyo\Ecommerce\Framework\Services\Shop;

use Validator;
use File;
use Image;
use Hideyo\Ecommerce\Framework\Services\Shop\Entity\ShopRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ShopService extends BaseService
{
	public function __construct(ShopRepository $shop)
	{
		$this->repo = $shop;
        $this->storageImagePath = storage_path() .config('hideyo.storage_path'). "/shop/";
        $this->publicImagePath = public_path() .config('hideyo.public_path'). "/shop/";
        
	} 

    /**
     * The validation rules for the model.
     *
     * @param  integer  $shopId id attribute model    
     * @return array
     */
    private function rules($shopId = false)
    {
        $rules = array(
            'title' => 'required|between:4,65|unique:'.$this->repo->getModel()->getTable(),
            'active' => 'required'
        );

        if ($shopId) {
            $rules['title'] =   $rules['title'].',title,'.$shopId;
        }

        return $rules;
    }

    public function create(array $attributes)
    {
        $validator = Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }
        $model = $this->updateOrAddModel($this->repo->getModel(), $attributes);
        
        if (isset($attributes['logo'])) {
            $destinationPath = $this->storageImagePath.$model->id;
            $filename =  str_replace(" ", "_", strtolower($attributes['logo']->getClientOriginalName()));
            $upload_success = $attributes['logo']->move($destinationPath, $filename);

            $attributes['logo_file_name'] = $filename;
            $attributes['logo_file_path'] = $upload_success->getRealPath();
            $model = $this->updateOrAddModel($model, $attributes);

            if (File::exists($model->logo_file_path)) {
                if (!File::exists($this->publicImagePath.$model->id)) {
                    File::makeDirectory($this->publicImagePath.$model->id, 0777, true);
                }

                if (!File::exists($this->publicImagePath.$model->id."/".$model->logo_file_name)) {
                    $image = Image::make($model->logo_file_path);
                    $image->interlace();
                    $image->save($this->publicImagePath.$model->id."/".$model->logo_file_name);
                }
            }
        }
        
        return $model;
    }

    public function updateById(array $attributes, $shopId)
    {
        $validator = Validator::make($attributes, $this->rules($shopId));

        if ($validator->fails()) {
            return $validator;
        }

        $model = $this->find($shopId);
        return $this->updateEntity($model, $attributes);
    }

    public function updateEntity($model, array $attributes = array())
    {
        if (count($attributes) > 0) {
            if (isset($attributes['logo'])) {
                File::delete($model->logo_file_path);
                $destinationPath = $this->storageImagePath.$model->id;

                $filename =  str_replace(" ", "_", strtolower($attributes['logo']->getClientOriginalName()));
                $upload_success = $attributes['logo']->move($destinationPath, $filename);

                $attributes['logo_file_name'] = $filename;
                $attributes['logo_file_path'] = $upload_success->getRealPath();
            }


            $model->slug = null;
            $model->fill($attributes);
            $model->save();

            if (File::exists($model->logo_file_path)) {
                if (!File::exists($this->publicImagePath.$model->id)) {
                    File::makeDirectory($this->publicImagePath.$model->id, 0777, true);
                }

                if (!File::exists($this->publicImagePath.$model->id."/".$model->logo_file_name)) {
                    $image = Image::make($model->logo_file_path);
                    $image->interlace();
                    $image->save($this->publicImagePath.$model->id."/".$model->logo_file_name);
                }
            }
        }

        return $model;
    }

    public function destroy($shopId)
    {
        $model = $this->find($shopId);

        File::deleteDirectory($this->publicImagePath.$model->id);
        $destinationPath = $this->storageImagePath.$model->id;
        File::deleteDirectory($destinationPath);
        
        $model->save();

        return $model->delete();
    }

    public function findUrl($shopUrl)
    {
        $result = $this->repo->findUrl($shopUrl);

        if (isset($result->id)) {
            return $result;
        }
        
        return false;      
    }
}