<?php
namespace Hideyo\Ecommerce\Framework\Services\Product\Entity;
 
use Hideyo\Ecommerce\Framework\Repositories\ProductImageRepository;
use Hideyo\Ecommerce\Framework\Services\Redirect\Entity\RedirectRepository;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\Product;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductImage;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductAttribute;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;

class ProductRepository extends BaseRepository
{

    protected $model;

    protected $guard = 'admin';


    public function __construct(Product $model, ProductImage $modelImage, RedirectRepository $redirect)
    {
        $this->model = $model;
        $this->modelImage = $modelImage;
        $this->redirect = $redirect;
    }

   
    public function selectByLimitAndOrderBy($shopId, $limit, $orderBy)
    {
        return $this->model->with(array('productCategory', 'relatedProducts', 'productImages' => function ($query) {
            $query->orderBy('rank', 'asc');
        }))->where('shop_id', '=', $shopId)->where('active', '=', 1)->limit($limit)->orderBy('id', $orderBy)->get();
    }

    function selectAllByShopId($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->get();
    }
    
    public function selectAllExport()
    {
        return $this->model->with(array('productImages' => function ($query) {
            $query->orderBy('rank', 'asc');
        }))->where('active', '=', 1)->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->get();
    }

    public function selectAllWithCombinations()
    {
        $result = $this->model->with(array('attributes'))->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->get();

        $newResult = array();
        foreach ($result as $product) {
            $newResult[$product->id] = $product->title;
            if ($product->attributes->count()) {
                foreach ($product->attributes as $attribute) {
                    $attributesArray = array();
                    foreach ($attribute->combinations as $combination) {
                        $attributesArray[] = $combination->attribute->value;
                    }

                    $newResult[$product->id.'-'.$attribute->id] = $product->title.' - '.implode(', ', $attributesArray);
                }
            }
        }

        return $newResult;
    }

    public function selectAllByProductParentId($productParentId)
    {
        return $this->model->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->where('product_parent_id', '=', $productParentId)->get();
    }

    public function selectOneById($productId)
    {
        $result = $this->model->with(array('productCategory', 'relatedProducts', 'productImages' => function ($query) {
            $query->orderBy('rank', 'asc');
        }))->where('active', '=', 1)->where('id', '=', $productId)->get()->first();
        return $result;
    }

  
    
    public function findImage($imageId)
    {
        return $this->modelImage->find($imageId);
    }

    public function getImageModel()
    {
        return $this->modelImage;
    }

    function selectAllByShopIdAndProductCategoryId($shopId, $productCategoryId, $filters = false)
    {
        $result = $this->model
        ->with(array('subcategories', 'extraFields' => function ($query) {
            $query->with('extraField')->orderBy('id', 'desc');
        }, 'taxRate', 'productCategory',  'relatedProducts' => function ($query) {
            $query->with('productImages')->orderBy('rank', 'asc');
        }, 'productImages' => function ($query) {
            $query->orderBy('rank', 'asc');
        }))
        ->where('shop_id', '=', $shopId)
        ->where('active', '=', 1)
                ->whereNotNull('product.product_category_id')
        ->where(function ($query) use ($productCategoryId) {
            $query->where('product_category_id', '=', $productCategoryId);
            $query->orWhereHas('subcategories', function ($query) use ($productCategoryId) {
                $query->where('product_category_id', '=', $productCategoryId);
            });
        });

        $result->orderBy(\DB::raw('product.rank = 0, '.'product.rank'), 'ASC');

        return $result->get();
    }

    public function selectOneByShopIdAndId($shopId, $productId = false, $attributeId = false)
    {
           return $this->model->with(
               array(
                'attributeGroup',
                'attributes' => function ($query) {
                    $query->with(
                        array(
                            'combinations' => function ($query) {
                                $query->with(
                                    array(
                                        'productAttribute',
                                        'attribute' => function ($query) {
                                            $query->with(
                                                array(
                                                    'attributeGroup'
                                                    )
                                            );
                                        }
                                        )
                                );
                            }
                            )
                    )->orderBy('default_on', 'desc');
                },
                'extraFields' => function ($query) {
                    $query->where(
                        'value',
                        '!=',
                        ''
                    )->orWhereNotNull('extra_field_default_value_id')->with(array('extraField',
                    'extraFieldDefaultValue'))->orderBy(
                        'id',
                        'desc'
                    );
                },
                'relatedProducts' => function ($query) {
                    $query->with(
                        'productImages',
                        'productCategory'
                    )->orderBy(
                        'rank',
                        'asc'
                    );
                },
                'productImages' => function ($query) {
                    $query->orderBy(
                        'rank',
                        'asc'
                    )->with(array('relatedProductAttributes',
                    'relatedAttributes'));
                })
           )->where('shop_id', '=', $shopId)->where('active', '=', 1)->whereNotNull('product_category_id')->where('id', '=', $productId)->get()->first();
    }

    public function ajaxProductImages($product, $combinationsIds, $productAttributeId = false) 
    {
        $images = array();

        if($product->productImages->count()) {  

            $images = $product->productImages()->has('relatedAttributes', '=', 0)->has('relatedProductAttributes', '=', 0)->orderBy('rank', '=', 'asc')->get();

            if($combinationsIds) {

                $imagesRelatedAttributes = ProductImage::
                whereHas('relatedAttributes', function($query) use ($combinationsIds, $product) { $query->with(array('productImage'))->whereIn('attribute_id', $combinationsIds); })
                ->where('product_id', '=', $product->id)
                ->get();

                if($imagesRelatedAttributes) {
                    $images = $images->merge($imagesRelatedAttributes)->sortBy('rank');
                }
                
            }

            if($productAttributeId) {

                $imagesRelatedProductAttributes = ProductImage::
                whereHas('relatedProductAttributes', function($query) use ($productAttributeId, $product) { $query->where('product_attribute_id', '=', $productAttributeId); })
                ->where('product_id', '=', $product->id)
                ->get();

                if($imagesRelatedProductAttributes) {
                    $images = $images->merge($imagesRelatedProductAttributes)->sortBy('rank');
                }   

                
            }

            $images->toArray();
        }

        return $images;
    }
}