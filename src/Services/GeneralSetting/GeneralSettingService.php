<?php

namespace Hideyo\Ecommerce\Framework\Services\GeneralSetting;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\GeneralSetting\Entity\GeneralSettingRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class GeneralSettingService extends BaseService
{
	public function __construct(GeneralSettingRepository $generalSetting)
	{
		$this->repo = $generalSetting;
	} 
}