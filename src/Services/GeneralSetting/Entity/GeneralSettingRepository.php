<?php
namespace Hideyo\Ecommerce\Framework\Services\GeneralSetting\Entity;
 
use Hideyo\Ecommerce\Framework\Services\GeneralSetting\Entity\GeneralSetting;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class GeneralSettingRepository extends BaseRepository
{
    protected $model;

    public function __construct(GeneralSetting $model)
    {
        $this->model = $model;
    }
  
    public function selectOneByShopIdAndName($shopId, $name)
    {     
        $result = $this->model
        ->where('shop_id', '=', $shopId)->where('name', '=', $name)->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }
}