<?php namespace Hideyo\Ecommerce\Framework\Services\Order\Events\Handlers;

use Hideyo\Ecommerce\Framework\Services\Order\Events\OrderChangeStatus;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;

use Hideyo\Ecommerce\Framework\Services\Invoice\InvoiceFacade as InvoiceService;
use Hideyo\Ecommerce\Framework\Services\Product\ProductFacade as ProductService;
use Hideyo\Ecommerce\Framework\Services\Order\OrderFacade as OrderService;
use Hideyo\Ecommerce\Framework\Services\Product\ProductCombinationFacade as ProductCombinationService;

class HandleOrderStatusValidated
{
    /**
     * Handle the event.
     *
     * @param  OrderChangeStatus  $event
     * @return void
     */
    public function handle(OrderChangeStatus $event)
    {
        if ($event->status->generate_invoice_from_order) {

            $result = InvoiceService::generateInvoiceFromOrder($event->order->id);
            if (isset($result->id)) {
                flash('Invoice is generated');
            }
        } 

        if ($event->status->order_is_validated AND !$event->order->validated) {        
            OrderService::updateById(array('validated' => 1), $event->order->id);  
            ProductService::reduceAmounts($event->order->products);
            ProductCombinationService::reduceAmounts($event->order->products);      
            flash('Inventory updated: product amounts are reduced.');   
        }
    }
}