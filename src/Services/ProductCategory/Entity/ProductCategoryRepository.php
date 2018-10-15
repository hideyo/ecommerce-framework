<?php 

namespace Hideyo\Ecommerce\Framework\Services\ProductCategory\Entity;

use Hideyo\Ecommerce\Framework\Repositories\ProductCategoryImageRepository;
use Hideyo\Ecommerce\Framework\Services\Redirect\Entity\RedirectRepository;
use Hideyo\Ecommerce\Framework\Services\ProductCategory\Entity\ProductCategory;
use Hideyo\Ecommerce\Framework\Services\ProductCategory\Entity\ProductCategoryImage;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class ProductCategoryRepository extends BaseRepository 
{
    protected $model;

    public function __construct(ProductCategory $model, ProductCategoryImage $imageModel)
    {
        $this->model = $model;
        $this->imageModel = $imageModel;
    }
 
    public function rebuild()
    {
        return $this->model->rebuild(true);
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->orderBy('title', 'asc')->get();
    }

    public function selectAllProductPullDown()
    {
        return $this->model->whereNull('redirect_product_category_id')->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->orderBy('title', 'asc')->get();
    }

    public function ajaxSearchByTitle($query)
    {
        return $this->model->where('title', 'LIKE', '%'.$query.'%')->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->orderBy('title', 'asc')->get();
    }

    function selectAllByShopId($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->orderBy('title', 'asc')->get();
    }

    function selectAllActiveByShopId($shopId)
    {
         return $this->model->where('shop_id', '=', $shopId)->where('active', '=', 1)->orderBy('title', 'asc')->get();
    }

    function selectAllByShopIdAndRoot($shopId)
    {
         return $this->model->roots()->where('shop_id', '=', $shopId)->where('active', '=', 1)->orderBy('title', 'asc')->get();
    }  

    function selectCategoriesByParentId($shopId, $parentId, $imageTag = false)
    {
        $result = $this->model->where('id', '=', $parentId)->first()->children()
        ->with(
            array('productCategoryImages' => function ($query) use ($imageTag) {
                if ($imageTag) {
                    $query->where('tag', '=', $imageTag);
                }
                    $query->orderBy('rank', 'asc');
            }
        )
        )
        ->where('product_category.shop_id', '=', $shopId)
        ->where('product_category.parent_id', '=', $parentId)
        ->where('active', '=', '1')->get();

        if ($result->count()) {
            return $result;
        }
        
        return false;
    }

    function selectRootCategories($shopId, $imageTag)
    {
        $result = $this->model
        ->with(
            array('productCategoryImages' => function ($query) use ($imageTag) {
                if ($imageTag) {
                    $query->where('tag', '=', $imageTag);
                }
                    $query->orderBy('rank', 'asc');
            }
            )
        )
            ->where('product_category.shop_id', '=', $shopId)
            ->whereNull('product_category.parent_id')
            ->get();

        if ($result) {
            return $result;
        }
        
        return false;
    }

    public function entireTreeStructure($shopId)
    {
        return $this->model->where('shop_id', '=', $shopId)->get()->toHierarchy();
    }

    public function findImage($imageId)
    {
        return $this->imageModel->find($imageId);
    }

    public function getImageModel()
    {
        return $this->imageModel;
    }

    function selectOneByShopIdAndSlug($shopId, $slug, $imageTag = false)
    { 
        $result = $this->model->
        with(
            array(
                'products' => function ($query) {
                    $query->where('active', '=', 1)->with(
                        array('productImages' => function ($query) {
                            $query->orderBy('rank', 'asc');
                        })
                    );
                },
                'productCategoryImages' => function ($query) use ($imageTag) {
                    if ($imageTag) {
                        $query->where('tag', '=', $imageTag);
                    } $query->orderBy('rank', 'asc');
                }, 
                'refProductCategory'
            )
        )
        ->where('product_category.shop_id', '=', $shopId)
        ->where('product_category.slug', '=', $slug)
        ->where('product_category.active', '=', 1)
        ->get()
        ->first();

        if ($result) {

            if ($result->isRoot()) {
                $result['is_root'] = true;
            }

            if ($result->isLeaf()) {
                $result['is_leaf'] = true;
            } else {
                $result['is_leaf'] = false;
                $result['children_product_categories'] = $result->children()->get();
            }

            return $result;
        }
        
        return false;        
    }
}