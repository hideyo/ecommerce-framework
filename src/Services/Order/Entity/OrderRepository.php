<?php
namespace Hideyo\Ecommerce\Framework\Services\Order\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Order\Entity\Order;
use Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderProduct;
use Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderAddress;
use Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderSendingMethod;
use Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderPaymentMethod;
use DB;
use Carbon\Carbon;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class OrderRepository extends BaseRepository 
{
    protected $model;

    public function __construct(
        Order $model,
        OrderProduct $modelOrderProduct,
        OrderAddress $modelOrderAddress,
        OrderSendingMethod $modelOrderSendingMethod,
        OrderPaymentMethod $modelOrderPaymentMethod
    ) {
        $this->model = $model;
        $this->modelOrderProduct = $modelOrderProduct;
        $this->modelOrderAddress = $modelOrderAddress;
        $this->modelOrderSendingMethod = $modelOrderSendingMethod;
        $this->modelOrderPaymentMethod = $modelOrderPaymentMethod;
    }
  
    public function getProductModel()
    {
        return $this->modelOrderProduct;
    }

    public function getAddressModel()
    {
        return $this->modelOrderAddress;
    }

    public function getOrderSendingMethodModel()
    {
        return $this->modelOrderSendingMethod;
    }

    public function getOrderPaymentMethodModel()
    {
        return $this->modelOrderPaymentMethod;
    }

    public function selectAllByShopIdAndStatusId($orderStatusId, $startDate = false, $endDate = false, $shopId = false)
    {
        $query = $this->model
        ->where('shop_id', auth('hideyobackend')->user()->selected_shop_id)
        ->where('order_status_id', $orderStatusId);

        if ($startDate) {
            $dt = Carbon::createFromFormat('d/m/Y', $startDate);
            $query->where('created_at', '>=', $dt->toDateString('Y-m-d'));
        }

        if ($endDate) {
            $dt = Carbon::createFromFormat('d/m/Y', $endDate);
            $query->where('created_at', '<=', $dt->toDateString('Y-m-d'));
        }

        $query->orderBy('created_at', 'ASC');
        return $query->get();
    }

    public function selectAllByAllProductsAndProductCategoryId($productCategoryId)
    {
        return $this->model->select('extra_field.*')->leftJoin('product_category_related_extra_field', 'extra_field.id', '=', 'product_category_related_extra_field.extra_field_id')->where('all_products', '=', 1)->orWhere('product_category_related_extra_field.product_category_id', '=', $productCategoryId)->get();
    }

    public function orderProductsByClientId($clientId, $shopId)
    {
        return $this->modelOrderProduct->with(array('product'))->whereHas('Order', function ($query) use ($clientId, $shopId) {
            $query->where('client_id', '=', $clientId)->where('shop_id', $shopId);
        });
    }

    public function selectAllByCompany()
    {
        return $this->model->leftJoin('shop', 'order.shop_id', '=', 'shop.id')->get();
    }

    public function productsByOrderIds(array $orderIds) 
    {
        $result = DB::table('order_product')
        ->select(DB::raw('DISTINCT(CONCAT_WS(\' - \',order_product.title, IFNULL(order_product.product_attribute_title, \'\'))) as title, order_product.product_attribute_title, order_product.reference_code, order_product.price_with_tax, order_product.price_without_tax,  SUM(order_product.amount) as total_amount'))
        ->whereIn('order_product.order_id', $orderIds)
        ->whereNotNull('order_product.product_attribute_title')
        ->groupBy('order_product.title', 'order_product.product_attribute_title')
        ->get();


        $result2 = DB::table('order_product')
        ->select(DB::raw('DISTINCT(order_product.title) as title, order_product.product_attribute_title, order_product.reference_code, order_product.price_with_tax, order_product.price_without_tax,  SUM(order_product.amount) as total_amount'))
        ->whereIn('order_product.order_id', $orderIds)
        ->whereNull('order_product.product_attribute_title')
        ->groupBy('order_product.title', 'order_product.product_attribute_title')
        ->get();

        $result = array_merge($result, $result2);
        return $result;
    }
}