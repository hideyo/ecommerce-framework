<?php 

namespace Hideyo\Ecommerce\Framework\Services\Client\Entity;
 
use Hideyo\Ecommerce\Framework\Services\Client\Entity\ClientAddress;
use File;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class ClientAddressRepository extends BaseRepository
{

    protected $model;

    public function __construct(ClientAddress $model)
    {
        $this->model = $model;
    }
  
    public function selectAllByClientId($clientId)
    {
         return $this->model->where('client_id', '=', $clientId)->get();
    }

    public function selectOneByClientIdAndId($clientId, $id)
    {
        return $this->model->where('client_id', '=', $clientId)->where('id', '=', $id)->get()->first();
    }
}