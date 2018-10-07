<?php namespace Hideyo\Ecommerce\Framework\Services\Order\Events;

use Hideyo\Ecommerce\Framework\Services\Order\Events\Event;
use Hideyo\Ecommerce\Framework\Services\Order\Entity\Order;

use Illuminate\Queue\SerializesModels;

class OrderChangeStatus extends Event
{
    use SerializesModels;

    public $order;
    public $status;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->status = $order->orderStatus;
    }
}