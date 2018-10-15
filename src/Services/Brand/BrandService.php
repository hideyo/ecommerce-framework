<?php

namespace Hideyo\Ecommerce\Framework\Services\Brand;

use Validator;
use Image;
use File;
use Hideyo\Ecommerce\Framework\Services\Brand\Entity\BrandRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
use Hideyo\Ecommerce\Framework\Services\Shop\ShopFacade as ShopService;
 
class BrandService extends BaseService
{
	public function __construct(BrandRepository $brand)
	{
		$this->repo = $brand;
        $this->storageImagePath = storage_path() .config('hideyo.storage_path'). "/brand/";
        $this->publicImagePath = public_path() .config('hideyo.public_path'). "/brand/";
	} 

    /**
     * The validation rules for the model.
     *
     * @param  integer  $brandId id attribute model    
     * @return array
     */
    private function rules($brandId = false, $attributes = false)
    {
        if (isset($attributes['seo'])) {
            $rules = array(
                'meta_title' => 'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id'
            );

            return $rules;
        } 

        $rules = array(
            'title' => 'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id',
            'rank'  => 'required|integer'
        );
        
        if ($brandId) {
            $rules['title'] =   $rules['title'].', '.$brandId.' = id';
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

    public function createImage(array $attributes, $brandId)
    {
        $userId = auth('hideyobackend')->user()->id;
        $shopId = auth('hideyobackend')->user()->selected_shop_id;
        $shop = ShopService::find($shopId);
       
        $rules = array(
            'file'=>'required|image|max:1000',
            'rank' => 'required'
        );

        $validator = Validator::make($attributes, $rules);

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = $userId;

        $destinationPath = $this->storageImagePath.$brandId;
        $attributes['user_id'] = $userId;
        $attributes['brand_id'] = $brandId;
        $attributes['extension'] = $attributes['file']->getClientOriginalExtension();
        $attributes['size'] = $attributes['file']->getSize();

        $filename =  str_replace(" ", "_", strtolower($attributes['file']->getClientOriginalName()));
        $uploadSuccess = $attributes['file']->move($destinationPath, $filename);

        if ($uploadSuccess) {
            $attributes['file'] = $filename;
            $attributes['path'] = $uploadSuccess->getRealPath();
     
            $this->repo->getModelImage()->fill($attributes);
            $this->repo->getModelImage()->save();

            if ($shop->thumbnail_square_sizes) {
                $sizes = explode(',', $shop->thumbnail_square_sizes);
                if ($sizes) {
                    foreach ($sizes as $valueSize) {
                        $image = Image::make($uploadSuccess->getRealPath());
                        $explode = explode('x', $valueSize);
                        $image->resize($explode[0], $explode[1]);
                        $image->interlace();

                        if (!File::exists($this->publicImagePath.$valueSize."/".$brandId."/")) {
                            File::makeDirectory($this->publicImagePath.$valueSize."/".$brandId."/", 0777, true);
                        }
                        $image->save($this->publicImagePath.$valueSize."/".$brandId."/".$filename);
                    }
                }
            }
            
            return $this->repo->getModelImage();
        }  
    }

    public function updateById(array $attributes, $brandId)
    {
        $validator = Validator::make($attributes, $this->rules($brandId, $attributes));
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        $model = $this->find($brandId);
        $model->fill($attributes);
        $model->save;
        return $model;
    }

    public function updateImageById(array $attributes, $brandId, $imageId)
    {
        $this->model = $this->find($brandId);

        if($this->model) {
            $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
            $image = $this->findImage($imageId);
            $image->fill($attributes);
            $image->save();

            return $image;
          
        }

        return false;
    }

    public function destroy($brandId)
    {
        $this->model = $this->find($brandId);

        // $url = $this->model->shop->url.route('brand.item', ['slug' => $this->model->slug], null);
        // $newUrl = $this->model->shop->url.route('brand.overview', array(), null);
        // $redirectResult = $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $newUrl, 'shop_id' => $this->model->shop_id));

        $this->model->save();
        return $this->model->delete();
    }

    public function destroyImage($imageId)
    {
        $image = $this->findImage($imageId);
        $filename = $this->storageImagePath.$image->brand_id."/".$image->file;
        $shopId = auth('hideyobackend')->user()->selected_shop_id;
        $shop = ShopService::find($shopId);

        if (File::exists($filename)) {
            File::delete($filename);
            if ($shop->thumbnail_square_sizes) {
                $sizes = explode(',', $shop->thumbnail_square_sizes);
                if ($sizes) {
                    foreach ($sizes as $valueSize) {
                        File::delete($this->publicImagePath.$valueSize."/".$image->brand_id."/".$image->file);
                    }
                }
            }
        }

        return $image->delete();
    }


    public function findImage($imageId)
    {
        return $this->repo->findImage($imageId);
    }

    public function getModelImage()
    {
        return $this->repo->getModelImage();
    } 

    public function refactorAllImagesByShopId($shopId)
    {
        $result = $this->repo->getModelImage()->get();
        $shop = ShopService::find($shopId);
        if($result AND $shop->thumbnail_square_sizes) {
            foreach ($result as $productImage) {
                if ($shop->thumbnail_square_sizes) {
                    $sizes = explode(',', $shop->thumbnail_square_sizes);
                    if ($sizes) {
                        foreach ($sizes as $valueSize) {
                            if (!File::exists($this->publicImagePath.$valueSize."/".$productImage->brand_id."/")) {
                                File::makeDirectory($this->publicImagePath.$valueSize."/".$productImage->brand_id."/", 0777, true);
                            }

                            if (!File::exists($this->publicImagePath.$valueSize."/".$productImage->brand_id."/".$productImage->file)) {
                                if (File::exists($this->storageImagePath.$productImage->brand_id."/".$productImage->file)) {
                                    $image = Image::make($this->storageImagePath.$productImage->brand_id."/".$productImage->file);
                                    $explode = explode('x', $valueSize);
                                    $image->fit($explode[0], $explode[1]);
                                    $image->interlace();
                                    $image->save($this->publicImagePath.$valueSize."/".$productImage->brand_id."/".$productImage->file);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}