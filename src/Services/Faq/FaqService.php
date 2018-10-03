<?php

namespace Hideyo\Ecommerce\Framework\Services\Faq;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\Faq\Entity\FaqRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class FaqService extends BaseService
{
	public function __construct(FaqRepository $faq)
	{
		$this->repo = $faq;
	} 
}