<?php

namespace Hideyo\Ecommerce\Framework\Services\Product;
 
use App\Product;
use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ProductService extends BaseService
{
	public function __construct(ProductRepository $product)
	{
		$this->repo = $product;
        $this->storageImagePath = storage_path() .config('hideyo.storage_path'). "/product/";
        $this->publicImagePath = public_path() .config('hideyo.public_path'). "/product/";

	} 


 /**
     * The validation rules for the model.
     *
     * @param  integer  $productId id attribute model    
     * @return array
     */
    private function rules($productId = false, $attributes = false)
    {
        if (isset($attributes['seo'])) {
            $rules = array(
                'meta_title'                 => 'required|between:4,65',
                'meta_description'           => 'required|between:4,160',
            );
        } elseif (isset($attributes['product-combination'])) {
            $rules = array();
        } elseif (isset($attributes['price'])) {
            $rules = array(
                'discount_start_date' => 'nullable|date_format:d/m/Y',
                'discount_end_date' => 'nullable|date_format:d/m/Y'
            );
        } else {
            $rules = array(
                'title'                 => 'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id',
                'amount'                => 'integer|required',
                'product_category_id'   => 'required|integer',
                'tax_rate_id'           => 'integer',
                'reference_code'      => 'required'
            );
            
            if ($productId) {
                $rules['title'] =   'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable().', shop_id, '.$productId.' = id';
            }
        }


        return $rules;
    }

    public function createCopy(array $attributes, $productId)
    {
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }
   
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        if (empty($attributes['discount_value'])) {
            $attributes['discount_value'] = null;
        }

        $this->model->fill($attributes);

        $this->model->save();

        if (isset($attributes['subcategories'])) {
            $this->model->subcategories()->sync($attributes['subcategories']);
        }
                
        return $this->model;
    }

  
    public function create(array $attributes)
    {
        
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }
   
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        if (empty($attributes['discount_value'])) {
            $attributes['discount_value'] = null;
        }

        $this->model->fill($attributes);
        $this->model->save();
        if (isset($attributes['subcategories'])) {
            $this->model->subcategories()->sync($attributes['subcategories']);
        }
        
        $this->model->addAllToIndex();

        return $this->model;
    }

    public function createImage(array $attributes, $productId)
    {
        $userId = auth('hideyobackend')->user()->id;
        $shopId = auth('hideyobackend')->user()->selected_shop_id;
        $shop = ShopService::find($shopId);
        $attributes['modified_by_user_id'] = $userId;

        $destinationPath = $this->storageImagePath.$productId;
        $attributes['user_id'] = $userId;
        $attributes['product_id'] = $productId;

        $rules = array(
            'file'=>'required|image|max:1000',
            'rank' => 'required'
        );

        foreach ($attributes['files'] as $file) {

            $attributes['file'] = $file;
            $validator = Validator::make($attributes, $rules);

            if ($validator->fails()) {
                return $validator;
            }

            $attributes['extension'] = $file->getClientOriginalExtension();
            $attributes['size'] = $file->getSize();
            $filename = str_replace(" ", "_", strtolower($file->getClientOriginalName()));
            $uploadSuccess = $file->move($destinationPath, $filename);

            if ($uploadSuccess) {
                $attributes['file'] = $filename;
                $attributes['path'] = $uploadSuccess->getRealPath();
                $file = new ProductImage;
                $file->fill($attributes);
                $file->save();
                if ($shop->thumbnail_square_sizes) {
                    $sizes = explode(',', $shop->thumbnail_square_sizes);
                    if ($sizes) {
                        foreach ($sizes as $valueSize) {
                            $image = Image::make($uploadSuccess->getRealPath());
                            $explode = explode('x', $valueSize);

                            if ($image->width() >= $explode[0] and $image->height() >= $explode[1]) {
                                $image->resize($explode[0], $explode[1]);
                            }

                            if (!File::exists($this->publicImagePath.$valueSize."/".$productId."/")) {
                                File::makeDirectory($this->publicImagePath.$valueSize."/".$productId."/", 0777, true);
                            }

                            $image->interlace();

                            $image->save($this->publicImagePath.$valueSize."/".$productId."/".$filename);
                        }
                    }
                }
            } 
        }

        if (isset($attributes['productAttributes'])) {
            $file->relatedProductAttributes()->sync($attributes['productAttributes']);
        }

        if (isset($attributes['attributes'])) {
            $this->model->relatedAttributes()->sync($attributes['attributes']);
        }

        $file->save();
        return $file;
    }

    public function refactorAllImagesByShopId($shopId)
    {
        $result = $this->modelImage->get();
        $shop = ShopService::find($shopId);
        foreach ($result as $productImage) {
            if ($shop->thumbnail_square_sizes) {
                $sizes = explode(',', $shop->thumbnail_square_sizes);
                if ($sizes) {
                    foreach ($sizes as $valueSize) {
                        if (!File::exists($this->publicImagePath.$valueSize."/".$productImage->product_id."/")) {
                            File::makeDirectory($this->publicImagePath.$valueSize."/".$productImage->product_id."/", 0777, true);
                        }

                        if (!File::exists($this->publicImagePath.$valueSize."/".$productImage->product_id."/".$productImage->file)) {
                            if (File::exists($this->storageImagePath.$productImage->product_id."/".$productImage->file)) {
                                $image = Image::make($this->storageImagePath.$productImage->product_id."/".$productImage->file);
                                $explode = explode('x', $valueSize);
                                $image->fit($explode[0], $explode[1]);
                            
                                $image->interlace();

                                $image->save($this->publicImagePath.$valueSize."/".$productImage->product_id."/".$productImage->file);
                            }
                        }
                    }
                }
            }
        }
    }

    public function updateById(array $attributes, $productId)
    {
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rules($productId, $attributes));

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        $model = $this->find($productId);

        $oldTitle = $model->title;
        $oldSlug = $model->slug;
        $oldProductCategoryId = $model->product_category_id;
        $oldProductCategorySlug = $model->productCategory->slug;

        if (empty($attributes['leading_atrribute_group_id'])) {
            $attributes['leading_atrribute_group_id'] = null;
        }

        if (empty($attributes['discount_value'])) {
            $attributes['discount_value'] = null;
        }


        if (count($attributes) > 0) {
            $model->fill($attributes);
            $model->subcategories()->sync(array());
            
            if (isset($attributes['subcategories'])) {
                $model->subcategories()->sync($attributes['subcategories']);
            }

            $model->save();
        }

        if (isset($attributes['title']) and isset($attributes['product_category_id'])) {
            if (($oldTitle != $attributes['title']) or ($oldProductCategoryId != $attributes['product_category_id'])) {
                $url = $model->shop->url.route('product.item', ['productId' => $model->id, 'productSlug' => $oldSlug, 'categorySlug' => $oldProductCategorySlug], null);
                if ($model->active) {
                    $this->redirect->destroyByUrl($url);
                }
                
                $newUrl = $model->shop->url.route('product.item', ['productId' => $model->id, 'productSlug' => $model->slug, 'categorySlug' => $model->productCategory->slug], null);
                $redirectResult = $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $newUrl, 'shop_id' => $model->shop_id));
            }
        }

        if (!$model->active) {
            $url = $model->shop->url.route('product.item', ['productId' => $model->id, 'productSlug' => $model->slug, 'categorySlug' => $model->productCategory->slug], null);
            $productCategoryUrl = $model->shop->url.route('product-category', ['slug' => $model->productCategory->slug], null);
            $redirectResult = $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $productCategoryUrl, 'shop_id' => $model->shop_id));
        }

        return $model;
    }



    public function updateImageById(array $attributes, $productId, $imageId)
    {
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        $this->modelImage = $this->findImage($imageId);
        return $this->updateImageEntity($attributes);
    }

    public function updateImageEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->modelImage->fill($attributes);
            
            $this->modelImage->relatedProductAttributes()->sync(array());
            if (isset($attributes['productAttributes'])) {
                $this->modelImage->relatedProductAttributes()->sync($attributes['productAttributes']);
            }
            
            $this->modelImage->relatedAttributes()->sync(array());
            if (isset($attributes['attributes'])) {
                $this->modelImage->relatedAttributes()->sync($attributes['attributes']);
            }

            $this->modelImage->save();
        }

        return $this->modelImage;
    }


    public function destroy($productId)
    {
        $this->model = $this->find($productId);

        if ($this->model->productCategory) {
            $url = $this->model->shop->url.route('product.item', ['productId' => $this->model->id, 'productSlug' => $this->model->slug, 'categorySlug' => $this->model->productCategory->slug], null);
            $productCategoryUrl = $this->model->shop->url.route('product-category', ['slug' => $this->model->productCategory->slug], null);
            $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $productCategoryUrl, 'shop_id' => $this->model->shop_id));
        }


        if ($this->model->productImages()->count()) {
            foreach ($this->model->productImages()->get() as $image) {
                $this->productImage->destroy($image->id);
            }
        }


        $directory = $this->storageImagePath.$this->model->id;
        File::deleteDirectory($directory);

        File::deleteDirectory($this->publicImagePath.$this->model->id);
        $this->model->addAllToIndex();
        return $this->model->delete();
    }


    public function destroyImage($imageId)
    {
        $this->modelImage = $this->findImage($imageId);
        $filename = $this->modelImage->path;
        $shopId = auth('hideyobackend')->user()->selected_shop_id;
        $shop = ShopService::find($shopId);
        
        if (File::exists($filename)) {
            File::delete($filename);


            if ($shop->thumbnail_square_sizes) {
                $sizes = explode(',', $shop->thumbnail_square_sizes);
                if ($sizes) {
                    foreach ($sizes as $valueSize) {
                        File::delete($this->publicImagePath.$valueSize."/".$this->modelImage->product_id."/".$this->modelImage->file);
                    }
                }
            }
        }

        return $this->modelImage->delete();
    }


  public function increaseAmounts($products)
    {
        if ($products->count()) {
            foreach ($products as $product) {
                if ($product->product_id) {
                    $this->model = $this->find($product->product_id);
                    if ($this->model) {
                        $attributes = array(
                            'title' => $this->model->title,
                            'amount' => $this->model->amount + $product->amount
                        );
                    }
                }

                $this->model->fill($attributes);

    

                $this->model->save();
            }
        }
    }

    public function reduceAmounts($products)
    {
        if ($products->count()) {
            foreach ($products as $product) {
                if ($product->product_id) {
                    $this->model = $this->find($product->product_id);
                    if ($this->model) {
                        $attributes = array(
                            'title' => $this->model->title,
                            'amount' => $this->model->amount - $product->amount
                        );
                    }
                }

                $this->model->fill($attributes);

    

                $this->model->save();
            }
        }
    }

    public function changeActive($productId)
    {
        $this->model = $this->find($productId);

        if ($this->model) {

            $active = 1;
            
            if ($this->model->active) {
                $active = 0;
            }

            $attributes = array(
                'active' => $active
            );

            $this->model->fill($attributes);

            // if (!$this->model->active) {
            //     $url = $this->model->shop->url.route('product.item', ['productId' => $this->model->id, 'productSlug' => $this->model->slug, 'categorySlug' => $this->model->productCategory->slug], null);
            //     $productCategoryUrl = $this->model->shop->url.route('product-category', ['slug' => $this->model->productCategory->slug], null);
            //     $redirectResult = $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $productCategoryUrl, 'shop_id' => $this->model->shop_id));
            // } else {
            //     $url = $this->model->shop->url.route('product.item', ['productId' => $this->model->id, 'productSlug' => $this->model->slug, 'categorySlug' => $this->model->productCategory->slug], null);
            //     $this->redirect->destroyByUrl($url);
            // }

            return $this->model->save();
        }

        return false;
    }

    public function changeAmount($productId, $amount)
    {
        $this->model = $this->find($productId);

        if ($this->model) {
            $attributes = array(
                'amount' => $amount
            );

            $this->model->fill($attributes);
            return $this->model->save();
        }

        return false;
    }

    public function changeRank($productId, $rank)
    {
        $this->model = $this->find($productId);

        if ($this->model) {
            $attributes = array(
                'rank' => $rank
            );

            $this->model->fill($attributes);

            return $this->model->save();
        }

        return false;
    }



    public function selectOneByShopIdAndId($shopId, $productId, $attributeId = false)
    {
       return $this->repo->selectOneByShopIdAndId($shopId, $productId, $attributeId);
    }

    public function ajaxProductImages($product, $combinationsIds, $productAttributeId = false) 
    {
        return $this->repo->ajaxProductImages($product, $combinationsIds, $productAttributeId);
    }

    public  function selectAllByShopIdAndProductCategoryId($shopId, $productCategoryId, $filters = false)
    {
        return $this->repo->selectAllByShopIdAndProductCategoryId($shopId, $productCategoryId, $filters);
    }

}