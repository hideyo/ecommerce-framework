<?php 

namespace Hideyo\Ecommerce\Framework\Services\Coupon\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Coupon\Entity\Coupon;
use Hideyo\Ecommerce\Framework\Services\Coupon\Entity\CouponGroup;
use Carbon\Carbon;
use Validator;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class CouponRepository extends BaseRepository  
{

    protected $model;

    public function __construct(Coupon $model, CouponGroup $couponGroup)
    {
        $this->model = $model;
        $this->modelGroup = $couponGroup;
    }

    public function selectAllGroups()
    {
        return $this->modelGroup->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->get();
    }

    function selectOneByShopIdAndCode($shopId, $code)
    {
        $dt = Carbon::now('Europe/Amsterdam');
        $result = $this->model
        ->where('shop_id', '=', $shopId)
        ->where('active', '=', 1)
        ->where('code', '=', $code)
        // ->where('published_at', '<=', $dt->toDateString('Y-m-d'))
        // ->where('unpublished_at', '>=', $dt->toDateString('Y-m-d'))
        ->with(array('products', 'productCategories', 'sendingMethods', 'paymentMethods'))
        ->get();
        
        if ($result->isEmpty()) {
            return false;
        }
        return $result->first();
    }

    public function findGroup($groupId)
    {
        return $this->modelGroup->find($groupId);
    }
    
    public function getGroupModel()
    {
        return $this->modelGroup;
    }  
}