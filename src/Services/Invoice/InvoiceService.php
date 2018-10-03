<?php

namespace Hideyo\Ecommerce\Framework\Services\Invoice;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\Invoice\Entity\InvoiceRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class InvoiceService extends BaseService
{
	public function __construct(InvoiceRepository $invoice)
	{
		$this->repo = $invoice;
	} 
}