<?php
namespace Hideyo\Ecommerce\Framework\Services\Order\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Order\Entity\OrderStatus;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class OrderStatusRepository extends BaseRepository 
{

    protected $model;

    public function __construct(OrderStatus $model)
    {
        $this->model = $model;
    } 
}