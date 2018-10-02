<?php
namespace Hideyo\Ecommerce\Framework\Services\Shop\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Shop\Entity\Shop;
use File;
use Image;
use Validator;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;

class ShopRepository extends BaseRepository
{
    protected $model;

    public function __construct(Shop $model)
    {
        $this->model = $model;
        $this->storageImagePath = storage_path() .config('hideyo.storage_path'). "/shop/";
        $this->publicImagePath = public_path() .config('hideyo.public_path'). "/shop/";
    }
  
    public function selectAll()
    {
        return $this->model->get();
    }    

    public function checkByUrl($shopUrl)
    {
        $result = $this->model->where('url', '=', $shopUrl)->get()->first();

        if (isset($result->id)) {
            return $result;
        }
        
        return false;        
    }    
}