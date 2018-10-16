<?php namespace Hideyo\Ecommerce\Framework\Services\Order\Events\Handlers;

use Hideyo\Ecommerce\Framework\Services\Order\Events\OrderChangeStatus;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Mail;
use File;
use Hideyo\Ecommerce\Framework\Services\Invoice\InvoiceFacade as InvoiceService;
use Hideyo\Ecommerce\Framework\Services\GeneralSetting\GeneralSettingFacade as GeneralSettingService;
use Notification;

class HandleOrderStatusEmail
{
    /**
     * Handle the event.
     *
     * @param  OrderChangeStatus  $event
     * @return void
     */
    public function handle(OrderChangeStatus $event)
    {
        if($event->order->shop->wholesale) {

            if ($event->status->send_email_to_customer) {
    
                if ($event->status->orderStatusEmailTemplate) {

                    $destinationPath = storage_path() . "/app";
                    $orderStatusEmailFromResult = GeneralSettingService::selectOneByShopIdAndName($event->order->shop_id, 'order-status-email-from');
                    
                    $orderStatusEmailFrom = 'wholesale@philandphae.com';
                    if ($orderStatusEmailFromResult) {
                        $orderStatusEmailFrom = $orderStatusEmailFromResult->value;
                    }

                    $orderStatusEmailNameResult = GeneralSettingService::selectOneByShopIdAndName($event->order->shop_id, 'order-status-email-name');
                    
                    $orderStatusEmailName = 'Phil & Phae B2B';
                    if ($orderStatusEmailNameResult) {
                        $orderStatusEmailName = $orderStatusEmailNameResult->value;
                    }


                    $orderStatusEmailBcc = 'wholesale@philandphae.com';
                    $language = 'en';
                    if(strtoupper($event->order->orderBillAddress->country) == 'NL') {
                        $language = 'nl';
                    }    
            

                    Mail::send('frontend.email.order-status', ['content' => Cart::replaceTags($event->status->orderStatusEmailTemplate->translate($language)->content, $event->order)], function ($message) use ($event, $destinationPath, $orderStatusEmailFrom, $orderStatusEmailName, $orderStatusEmailBcc, $language) {
                        $message->from($orderStatusEmailFrom, $orderStatusEmailName);
                        $message->to($event->order->client->email, $event->order->orderBillAddress->firstname)->subject(Cart::replaceTags($event->status->orderStatusEmailTemplate->translate($language)->subject, $event->order));

                        if ($orderStatusEmailBcc) {
                            $message->bcc($orderStatusEmailBcc, $orderStatusEmailName);
                        }

                        if ($event->order and $event->status->attach_order_to_email) {
                            $pdfText = "";
                            $sign = '&euro;';
                            if($event->order->client->usd) {
                                $sign = '&#36;';
                            }


                            $pdf = \PDF::loadView('admin.order.pdf-wholesale', array('order' => $event->order, 'sign' => $sign, 'pdfText' => $pdfText))->setPaper('a4', 'landscape'); 
                            if (!File::exists($destinationPath.'/order/')) {
                                File::makeDirectory($destinationPath.'/order/', 0777, true);
                            }

                            $upload_success = $pdf->save($destinationPath.'/order/order-'.$event->order->generated_custom_order_id.'.pdf');
                            $message->attach($destinationPath.'/order/order-'.$event->order->generated_custom_order_id.'.pdf');
                            Notification::success('Email has order attachment');
                        }

                        Notification::success('Email with order status has been sent to '.$event->order->client->email.' from info@'.$orderStatusEmailFrom);
                    });

                    if ($event->status->attach_order_to_email) {
                        File::delete($destinationPath.'/order/order-'.$event->order->generated_custom_order_id.'.pdf');
                    }
                }

                if($event->order->type == 'pre_order') {

                }elseif($event->order->type == 'from_stock') {

                }

            }

        } else {
            if ($event->status->send_email_to_customer) {
                if ($event->status->orderStatusEmailTemplate) {
                    $destinationPath = storage_path() . "/app";
                    $orderStatusEmailFromResult = GeneralSettingService::selectOneByShopIdAndName($event->order->shop_id, 'order-status-email-from');
                    
                    $orderStatusEmailFrom = 'info@philandphae.com';
                    if ($orderStatusEmailFromResult) {
                        $orderStatusEmailFrom = $orderStatusEmailFromResult->value;
                    }

                    $orderStatusEmailNameResult = GeneralSettingService::selectOneByShopIdAndName($event->order->shop_id, 'order-status-email-name');
                    
                    $orderStatusEmailName = 'Phil & Phae';
                    if ($orderStatusEmailNameResult) {
                        $orderStatusEmailName = $orderStatusEmailNameResult->value;
                    }

                    $orderStatusEmailBccResult = GeneralSettingService::selectOneByShopIdAndName($event->order->shop_id, 'order-status-email-bcc');
                    $orderStatusEmailBcc = false;
                    
                    if ($orderStatusEmailBccResult) {
                        $orderStatusEmailBcc = $orderStatusEmailBccResult->value;
                    }

                    $language = 'en';
                    if(strtoupper($event->order->orderBillAddress->country) == 'NL') {
                        $language = 'nl';
                    }    

                    Mail::send('frontend.email.order-status', ['content' => Cart::replaceTags($event->status->orderStatusEmailTemplate->translate($language)->content, $event->order)], function ($message) use ($event, $destinationPath, $orderStatusEmailFrom, $orderStatusEmailName, $orderStatusEmailBcc, $language) {
                        $message->from($orderStatusEmailFrom, $orderStatusEmailName);
                        $message->to($event->order->client->email, $event->order->orderBillAddress->firstname)->subject(Cart::replaceTags($event->status->orderStatusEmailTemplate->translate($language)->subject, $event->order));

                        if ($orderStatusEmailBcc) {
                            $message->bcc($orderStatusEmailBcc, $orderStatusEmailName);
                        }

                        if ($event->order->invoice and $event->status->attach_invoice_to_email) {
                            $pdf = \PDF::loadView('admin.invoice.pdf-consumer', array('invoice' => InvoiceService::find($event->order->invoice->id)));
                            if (!File::exists($destinationPath.'/invoice/')) {
                                File::makeDirectory($destinationPath.'/invoice/', 0777, true);
                            }

                            $upload_success = $pdf->save($destinationPath.'/invoice/invoice-'.$event->order->invoice->generated_custom_invoice_id.'.pdf');
                            $message->attach($destinationPath.'/invoice/invoice-'.$event->order->invoice->generated_custom_invoice_id.'.pdf');
                            Notification::success('Email has invoice attachment');
                        }

                        if ($event->order and $event->status->attach_order_to_email) {
                            $text = $this->sendingPaymentMethodRelated->selectOneByShopIdAndPaymentMethodIdAndSendingMethodId($event->order->shop->id, $event->order->orderPaymentMethod->payment_method_id, $event->order->orderSendingMethod->sending_method_id);
                        
                            $pdfText = "";
                            if ($text) {
                                $pdfText = Cart::replaceTags($text->pdf_text, $event->order);
                            }

                            $pdf = \PDF::loadView('admin.order.pdf', array('order' => $event->order, 'pdfText' => $pdfText));
                            if (!File::exists($destinationPath.'/order/')) {
                                File::makeDirectory($destinationPath.'/order/', 0777, true);
                            }

                            $upload_success = $pdf->save($destinationPath.'/order/order-'.$event->order->generated_custom_order_id.'.pdf');
                            $message->attach($destinationPath.'/order/order-'.$event->order->generated_custom_order_id.'.pdf');
                            Notification::success('Email has order attachment');
                        }

                        Notification::success('Email with order status has been sent to '.$event->order->client->email.' from info@'.$orderStatusEmailFrom);
                    });

                    if ($event->status->attach_invoice_to_email AND $event->order->invoice) {
                        File::delete($destinationPath.'/invoice/invoice-'.$event->order->invoice->generated_custom_invoice_id.'.pdf');
                    }

                    if ($event->status->attach_order_to_email) {
                        File::delete($destinationPath.'/order/order-'.$event->order->generated_custom_order_id.'.pdf');
                    }
                }
            }
        }
    }
}