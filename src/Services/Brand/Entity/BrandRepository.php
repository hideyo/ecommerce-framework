<?php
namespace Hideyo\Ecommerce\Framework\Services\Brand\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Brand\Entity\Brand;
use Hideyo\Ecommerce\Framework\Services\Brand\Entity\BrandImage;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class BrandRepository extends BaseRepository 
{
    protected $model;

    public function __construct(
        Brand $model, 
        BrandImage $modelImage)
    {
        $this->model        = $model;
        $this->modelImage   = $modelImage;
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', auth('hideyobackend')->user()->selected_shop_id)->orderBy('title', 'asc')->get();
    } 
    
    public function findImage($imageId)
    {
        return $this->modelImage->find($imageId);
    }

    public function getModelImage()
    {
        return $this->modelImage;
    }    
}