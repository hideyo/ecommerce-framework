<?php

namespace Hideyo\Ecommerce\Framework\Services\Client;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\Client\Entity\ClientRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class ClientService extends BaseService
{
	public function __construct(ClientRepository $client)
	{
		$this->repo = $client;
	} 
}