<?php
namespace Hideyo\Ecommerce\Framework\Services\Product\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Product\Entity\ProductAmountSeries;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class ProductAmountSeriesRepository extends BaseRepository
{
    protected $model;

    public function __construct(ProductAmountSeries $model, ProductRepository $product)
    {
        $this->model = $model;
        $this->product = $product;
    } 
}