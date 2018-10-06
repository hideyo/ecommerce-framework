<?php
namespace Hideyo\Ecommerce\Framework\Services\Product\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductTagGroup;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class ProductTagGroupRepository extends BaseRepository 
{

    protected $model;

    public function __construct(ProductTagGroup $model)
    {
        $this->model = $model;
    }

  

    function selectAllByTagAndShopId($shopId, $tag)
    {
        $result = $this->model->with(array('relatedProducts' => function ($query) {
            $query->with(array('productCategory', 'productImages' => function ($query) {
                $query->orderBy('rank', 'asc');
            }))->where('active', '=', 1);
        }))->where('shop_id', '=', $shopId)->where('tag', '=', $tag)->get();
        if ($result->count()) {
            return $result->first()->relatedProducts;
        } else {
            return false;
        }
    } 
}