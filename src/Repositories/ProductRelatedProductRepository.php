<?php
namespace Hideyo\Ecommerce\Framework\Repositories;
 
use Hideyo\Ecommerce\Framework\Models\ProductRelatedProduct;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\Product;
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductRepository;
use Auth;
 
class ProductRelatedProductRepository  extends BaseRepository 
{

    protected $model;

    public function __construct(ProductRelatedProduct $model, ProductRepository $product)
    {
        $this->model = $model;
        $this->product = $product;
    }
  
    public function create(array $attributes, $productParentId)
    {
        $parentProduct = $this->product->find($productParentId);
   
        if (isset($attributes['products'])) {
            $parentProduct->relatedProducts()->attach($attributes['products']);
        }

        return $parentProduct->save();
    }

    function selectAllByProductId($productId)
    {
         return $this->model->where('product_id', '=', $productId)->get();
    }   
}