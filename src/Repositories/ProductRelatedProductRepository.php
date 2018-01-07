<?php
namespace Hideyo\Ecommerce\Framework\Repositories;
 
use Hideyo\Ecommerce\Framework\Models\ProductRelatedProduct;
use Hideyo\Ecommerce\Framework\Models\Product;
use Hideyo\Ecommerce\Framework\Repositories\ProductRepositoryInterface;
use Auth;
 
class ProductRelatedProductRepository  extends BaseRepository implements ProductRelatedProductRepositoryInterface
{

    protected $model;

    public function __construct(ProductRelatedProduct $model, ProductRepositoryInterface $product)
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