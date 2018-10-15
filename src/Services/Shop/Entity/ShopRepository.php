<?php
namespace Hideyo\Ecommerce\Framework\Services\Shop\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Shop\Entity\Shop;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;

class ShopRepository extends BaseRepository
{
    protected $model;

    public function __construct(Shop $model)
    {
        $this->model = $model;
    }
  
    public function selectAll()
    {
        return $this->model->get();
    }    

    public function findUrl($shopUrl)
    {
        return $result = $this->model->where('url', $shopUrl)->get()->first();     
    }    
}