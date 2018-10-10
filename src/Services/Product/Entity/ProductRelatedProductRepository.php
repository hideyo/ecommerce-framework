<?php
namespace Hideyo\Ecommerce\Framework\Services\Product\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductRelatedProduct;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\Product;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductRepository;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class ProductRelatedProductRepository  extends BaseRepository 
{

    protected $model;

    public function __construct(ProductRelatedProduct $model, ProductRepository $product)
    {
        $this->model = $model;
        $this->product = $product;
    }
  
    function selectAllByProductId($productId)
    {
         return $this->model->where('product_id', '=', $productId)->get();
    }   
}