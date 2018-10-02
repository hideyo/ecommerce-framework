<?php

namespace Hideyo\Ecommerce\Framework\Services\Shop;
 
use App\Product;
use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\Shop\Entity\ShopRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ShopService extends BaseService
{
	public function __construct(ShopRepository $shop)
	{
		$this->repo = $shop;
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

        $this->repo->getModel()->slug = null;
        $this->repo->getModel()->fill($attributes);
        $this->repo->getModel()->save();
        
        if (isset($attributes['logo'])) {
            $destinationPath = $this->storageImagePath.$this->repo->getModel()->id;
            $filename =  str_replace(" ", "_", strtolower($attributes['logo']->getClientOriginalName()));
            $upload_success = $attributes['logo']->move($destinationPath, $filename);

            $attributes['logo_file_name'] = $filename;
            $attributes['logo_file_path'] = $upload_success->getRealPath();
            $this->repo->getModel()->fill($attributes);
            $this->repo->getModel()->save();

            if (File::exists($this->repo->getModel()->logo_file_path)) {
                if (!File::exists($this->publicImagePath.$this->repo->getModel()->id)) {
                    File::makeDirectory($this->publicImagePath.$this->repo->getModel()->id, 0777, true);
                }

                if (!File::exists($this->publicImagePath.$this->repo->getModel()->id."/".$this->repo->getModel()->logo_file_name)) {
                    $image = Image::make($this->repo->getModel()->logo_file_path);
                    $image->interlace();
                    $image->save($this->publicImagePath.$this->repo->getModel()->id."/".$this->repo->getModel()->logo_file_name);
                }
            }
        }
        
        return $this->repo->getModel();
    }

    public function updateById(array $attributes, $shopId)
    {
        $validator = Validator::make($attributes, $this->rules($shopId));

        if ($validator->fails()) {
            return $validator;
        }

        $shopModel = $this->find($shopId);
        return $this->updateEntity($shopModel, $attributes);
    }

    public function updateEntity($shopModel, array $attributes = array())
    {
        if (count($attributes) > 0) {
            if (isset($attributes['logo'])) {
                File::delete($shopModel->logo_file_path);
                $destinationPath = $this->storageImagePath.$shopModel->id;

                $filename =  str_replace(" ", "_", strtolower($attributes['logo']->getClientOriginalName()));
                $upload_success = $attributes['logo']->move($destinationPath, $filename);

                $attributes['logo_file_name'] = $filename;
                $attributes['logo_file_path'] = $upload_success->getRealPath();
            }


            $shopModel->slug = null;
            $shopModel->fill($attributes);
            $shopModel->save();

            if (File::exists($shopModel->logo_file_path)) {
                if (!File::exists($this->publicImagePath.$shopModel->id)) {
                    File::makeDirectory($this->publicImagePath.$shopModel->id, 0777, true);
                }

                if (!File::exists($this->publicImagePath.$shopModel->id."/".$shopModel->logo_file_name)) {
                    $image = Image::make($shopModel->logo_file_path);
                    $image->interlace();
                    $image->save($this->publicImagePath.$shopModel->id."/".$shopModel->logo_file_name);
                }
            }
        }

        return $shopModel;
    }

    public function destroy($shopId)
    {
        $shopModel = $this->find($shopId);
        File::deleteDirectory($this->publicImagePath.$shopModel->id);

        $destinationPath = $this->storageImagePath.$shopModel->id;

        File::deleteDirectory($destinationPath);
        $shopModel->save();

        return $shopModel->delete();
    }


    public function checkByUrl($shopUrl)
    {
        return $this->repo->checkByUrl($shopUrl);
    }
}