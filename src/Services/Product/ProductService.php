<?php

namespace Hideyo\Ecommerce\Framework\Services\Product;
 
use App\Product;
use Validator;
use File;
use Image;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductRepository;
use Hideyo\Ecommerce\Framework\Services\Shop\ShopFacade as ShopService;
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

        $this->getModel()->fill($attributes);

        $this->getModel()->save();

        if (isset($attributes['subcategories'])) {
            $this->getModel()->subcategories()->sync($attributes['subcategories']);
        }
                
        return $this->getModel();
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

        $this->getModel()->fill($attributes);
        $this->getModel()->save();
        if (isset($attributes['subcategories'])) {
            $this->getModel()->subcategories()->sync($attributes['subcategories']);
        }
        
        $this->getModel()->addAllToIndex();

        return $this->getModel();
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
                $file = $this->repo->getImageModel();
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
        $result = $this->getImageModel()->get();
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
        $image = $this->findImage($imageId);

        if (count($attributes) > 0) {
            $image->fill($attributes);
            
            $image->relatedProductAttributes()->sync(array());
            if (isset($attributes['productAttributes'])) {
                $image->relatedProductAttributes()->sync($attributes['productAttributes']);
            }
            
            $image->relatedAttributes()->sync(array());
            if (isset($attributes['attributes'])) {
                $image->relatedAttributes()->sync($attributes['attributes']);
            }

            $image->save();
        }

        return $image;
    }

    public function destroy($productId)
    {
        $model = $this->find($productId);

        if ($model->productCategory) {
            $url = $model->shop->url.route('product.item', ['productId' => $model->id, 'productSlug' => $model->slug, 'categorySlug' => $model->productCategory->slug], null);
            $productCategoryUrl = $model->shop->url.route('product-category', ['slug' => $model->productCategory->slug], null);
            $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $productCategoryUrl, 'shop_id' => $model->shop_id));
        }


        if ($model->productImages()->count()) {
            foreach ($model->productImages()->get() as $image) {
                $this->productImage->destroy($image->id);
            }
        }


        $directory = $this->storageImagePath.$model->id;
        File::deleteDirectory($directory);

        File::deleteDirectory($this->publicImagePath.$model->id);
        $model->addAllToIndex();
        return $model->delete();
    }


    public function destroyImage($imageId)
    {
        $modelImage = $this->findImage($imageId);
        $filename = $modelImage->path;
        $shopId = auth('hideyobackend')->user()->selected_shop_id;
        $shop = ShopService::find($shopId);
        
        if (File::exists($filename)) {
            File::delete($filename);


            if ($shop->thumbnail_square_sizes) {
                $sizes = explode(',', $shop->thumbnail_square_sizes);
                if ($sizes) {
                    foreach ($sizes as $valueSize) {
                        File::delete($this->publicImagePath.$valueSize."/".$modelImage->product_id."/".$modelImage->file);
                    }
                }
            }
        }

        return $modelImage->delete();
    }


    public function priceDetails($product, $field)
    {
        $preSaleDiscount = session()->get('preSaleDiscount');

        if ($product->price) {

            $taxRate = 0;
            $priceInc = 0;
            $taxValue = 0;

            if (isset($product->taxRate)) {
                $taxRate = $product->taxRate->rate;        
                $priceInc = (($product->taxRate->rate / 100) * $product->price) + $product->price;
                $taxValue = $priceInc - $product->price;
            }

            $discountPriceInc = $priceInc;
            $discountPriceEx = $product->price;
            $discountTaxRate = 0;

            if($preSaleDiscount) {

                if ($preSaleDiscount['value'] AND $preSaleDiscount['collection_id'] == $product->collection_id) {

                    if ($preSaleDiscount['discount_way'] == 'amount') {
                        $discountPriceInc = $priceInc - $product->value;
                        $discountPriceEx = $discountPriceInc / 1.21;
                    } elseif ($preSaleDiscount['discount_way'] == 'percent') {
          
                        $tax = ($preSaleDiscount['value'] / 100) * $priceInc;
                        $discountPriceInc = $priceInc - $tax;
                        $discountPriceEx = $discountPriceInc / 1.21;                       
                    }
                    $discountTaxRate = $discountPriceInc - $discountPriceEx;                   
                }

                if($preSaleDiscount['products']) {

                    $productIds = array_column($preSaleDiscount['products'], 'id');

                    if (in_array($product->id, $productIds) OR (isset($product->product_id) AND in_array($product->product_id, $productIds))) {

                        if ($preSaleDiscount['discount_way'] == 'amount') {
                            $discountPriceInc = $priceInc - $product->value;
                            $discountPriceEx = $discountPriceInc / 1.21;
                        } elseif ($preSaleDiscount['discount_way'] == 'percent') {
              
                            $tax = ($preSaleDiscount['value'] / 100) * $priceInc;
                            $discountPriceInc = $priceInc - $tax;
                            $discountPriceEx = $discountPriceInc / 1.21;                       
                        }
                        $discountTaxRate = $discountPriceInc - $discountPriceEx;
                    }

                }
            } else {
                if ($product->discount_value) {
                    if ($product->discount_type == 'amount') {

                        $discountPriceInc = $priceInc - $product->discount_value;
                        $discountPriceEx = $discountPriceInc / 1.21;
                    } elseif ($product->discount_type == 'percent') {

                        $tax = ($product->discount_value / 100) * $priceInc;
                        $discountPriceInc = $priceInc - $tax;
                        $discountPriceEx = $discountPriceInc / 1.21;
                    }
                    $discountTaxRate = $discountPriceInc - $discountPriceEx;
                }
            }

            $productArray = array(
                'original_price_ex_tax'  => $product->price,
                'original_price_ex_tax_number_format'  => number_format($product->price, 2, '.', ''),
                'original_price_inc_tax' => $priceInc,
                'original_price_inc_tax_number_format' => number_format($priceInc, 2, '.', ''),
                'tax_rate' => $taxRate,
                'tax_value' => $taxValue,
                'currency' => 'EU',
                'discount_price_inc' => $discountPriceInc,
                'discount_price_inc_number_format' => number_format($discountPriceInc, 2, '.', ''),
                'discount_price_ex' => $discountPriceEx,
                'discount_price_ex_number_format' => number_format($discountPriceEx, 2, '.', ''),
                'discount_tax_value' => $discountTaxRate,
                'discount_value' => $product->discount_value,
                'amount' => $product->amount
            );
        
            if (isset($productArray[$field])) {
                return $productArray[$field];
            }         
        } 

        return false;
    }

    public function getImage($productId, $combinationsIds, $productAttributeId = false)
    {
        $product = $this->getModel();
        $product = $product->has('productImages')->where('id', '=', $productId)->first();
        $images = array();

        if($product AND $product->productImages->count()) {  

            $images = $product->productImages()->has('relatedAttributes', '=', 0)->has('relatedProductAttributes', '=', 0)->orderBy('rank', '=', 'asc')->get();

            if($combinationsIds) {

                $imagesRelatedAttributes = $this->getImageModel()->
                whereHas('relatedAttributes', function($query) use ($combinationsIds, $product, $productId) { $query->with(array('productImage'))->whereIn('attribute_id', $combinationsIds); });
                
                $images = $images->merge($imagesRelatedAttributes)->sortBy('rank');          
            }

            if($productAttributeId) {                
                $imagesRelatedProductAttributes = $this->getImageModel()->
                whereHas('relatedProductAttributes', function($query) use ($productAttributeId, $product) { $query->where('product_attribute_id', '=', $productAttributeId); })
                ->where('product_id', '=', $productId)
                ->get();

                $images = $images->merge($imagesRelatedProductAttributes)->sortBy('rank');
            }


            if(!$images->count()) {
                $images = $product->productImages()->orderBy('rank', '=', 'asc')->get();
            }

            if ($images->count()) {
                return $images->first()->file;
            }
        }
    }


  public function increaseAmounts($products)
    {
        if ($products->count()) {
            foreach ($products as $product) {
                if ($product->product_id) {
                    $model = $this->find($product->product_id);
                    if ($model) {
                        $attributes = array(
                            'title' => $model->title,
                            'amount' => $model->amount + $product->amount
                        );
                    }
                }

                $model->fill($attributes);

    

                $model->save();
            }
        }
    }

    public function reduceAmounts($products)
    {
        if ($products->count()) {
            foreach ($products as $product) {
                if ($product->product_id) {
                    $model = $this->find($product->product_id);
                    if ($model) {
                        $attributes = array(
                            'title' => $model->title,
                            'amount' => $model->amount - $product->amount
                        );
                    }
                }

                $model->fill($attributes);
                $model->save();
            }
        }
    }

    public function changeActive($productId)
    {
        $model = $this->find($productId);

        if ($model) {

            $active = 1;
            
            if ($model->active) {
                $active = 0;
            }

            $attributes = array(
                'active' => $active
            );

            $model->fill($attributes);

            // if (!$model->active) {
            //     $url = $model->shop->url.route('product.item', ['productId' => $model->id, 'productSlug' => $model->slug, 'categorySlug' => $model->productCategory->slug], null);
            //     $productCategoryUrl = $model->shop->url.route('product-category', ['slug' => $model->productCategory->slug], null);
            //     $redirectResult = $this->redirect->create(array('active' => 1, 'url' => $url, 'redirect_url' => $productCategoryUrl, 'shop_id' => $model->shop_id));
            // } else {
            //     $url = $model->shop->url.route('product.item', ['productId' => $model->id, 'productSlug' => $model->slug, 'categorySlug' => $model->productCategory->slug], null);
            //     $this->redirect->destroyByUrl($url);
            // }

            return $model->save();
        }

        return false;
    }

    public function changeAmount($productId, $amount)
    {
        $model = $this->find($productId);

        if ($model) {
            $attributes = array(
                'amount' => $amount
            );

            $model->fill($attributes);
            return $model->save();
        }

        return false;
    }

    public function changeRank($productId, $rank)
    {
        $model = $this->find($productId);

        if ($model) {
            $attributes = array(
                'rank' => $rank
            );

            $model->fill($attributes);

            return $model->save();
        }

        return false;
    }


    public function selectOneById($productId)
    {
        return $this->repo->selectOneById($productId);
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

    public function findImage($imageId)
    {
        return $this->repo->findImage($imageId);
    }

    public function getImageModel()
    {
        return $this->repo->getImageModel();
    }


}