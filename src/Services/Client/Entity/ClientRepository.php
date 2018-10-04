<?php 

namespace Hideyo\Ecommerce\Framework\Services\Client\Entity;

use Hideyo\Ecommerce\Framework\Services\Client\Entity\Client;
use Hideyo\Ecommerce\Framework\Services\Client\Entity\ClientAddressRepository;
use Mail;
use Carbon\Carbon;
use Validator;
use Hash;
use Hideyo\Ecommerce\Framework\Services\Shop\ShopFacade as ShopService;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;

class ClientRepository extends BaseRepository 
{

    protected $model;

    public function __construct(
        Client $model,  
        ClientAddressRepository $clientAddress)
    {
        $this->model = $model;
        $this->clientAddress = $clientAddress;
    }

    public function selectAll()
    {
        return $this->model->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->get();
    }

    public function selectAllByBillClientAddress()
    {
        return $this->model->selectRaw('CONCAT(client_address.firstname, " ", client_address.lastname) as fullname, client_address.*, client.id')
        ->leftJoin('client_address', 'client.bill_client_address_id', '=', 'client_address.id')->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)
        ->get();
    }

    public function findByEmail($email, $shopId)
    {
        $client = $this->model->where('shop_id', '=', $shopId)->where('email', '=', $email)->get()->first();
        return $client;
    }

    public function checkEmailByShopIdAndNoAccountCreated($email, $shopId) {
        return $this->model->where('shop_id', '=', $shopId)->whereNotNull('account_created')->where('email', '=', $email)->get()->first();
    }

    public function checkEmailByShopId($email, $shopId)
    {
        return $this->model->where('shop_id', '=', $shopId)->where('email', '=', $email)->get()->first();
    }

    public function validateRegisterNoAccount(array $attributes, $shopId)
    {
        $client = $this->model->where('shop_id', '=', $shopId)->where('email', '=', $attributes['email'])->get()->first();

        if ($client) {
            return false;
        }

        return true;
    }

    public function selectOneByShopIdAndId($shopId, $clientId)
    {
        $result = $this->model->with(array('clientAddress', 'clientDeliveryAddress', 'clientBillAddress'))->where('shop_id', '=', $shopId)->where('active', '=', 1)->where('id', '=', $clientId)->first();
        return $result;
    }

    public function selectOneById($clientId)
    {
        $result = $this->model->with(array('clientAddress', 'clientDeliveryAddress', 'clientBillAddress'))->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->where('active', '=', 1)->where('id', '=', $clientId)->first();
        return $result;
    }

    public function getClientByConfirmationCode($shopId, $email, $confirmationCode)
    {
        return $this->model->where('shop_id', '=', $shopId)->where('email', '=', $email)->where('confirmation_code', '=', $confirmationCode)->get()->first();
    }

    public function activate($clientId)
    {
        $this->model = $this->model->where('id', '=', $clientId)->get()->first();

        if ($this->model) {
            $attributes['confirmed'] = 1;
            $attributes['active'] = 1;
            $attributes['confirmation_code'] = null;
            
            return $this->updateEntity($attributes);
        }
        
        return false;
    }

    public function deactivate($clientId)
    {
        $this->model = $this->model->where('id', '=', $clientId)->get()->first();

        if ($this->model) {
            $attributes['confirmed'] = 0;
            $attributes['active'] = 0;
            $attributes['confirmation_code'] = null;
            
            return $this->updateEntity($attributes);
        }
        
        return false;
    }

    public function validateConfirmationCodeByConfirmationCodeAndEmail($confirmationCode, $email, $shopId)
    {
        return $this->model
        ->where('shop_id', '=', $shopId)
        ->where('email', '=', $email)
        ->whereNotNull('account_created')
        ->where('confirmation_code', '=', $confirmationCode)
        ->get()->first();
    }

    public function validateConfirmationCodeByConfirmationCodeAndNewEmail($confirmationCode, $newEmail, $shopId)
    {
        return $this->model
        ->where('shop_id', '=', $shopId)
        ->where('new_email', '=', $newEmail)
        ->whereNotNull('account_created')
        ->where('confirmation_code', '=', $confirmationCode)
        ->get()->first();
    }

    public function updateLastLogin($clientId)
    {
        $check = $this->model->where('id', '=', $clientId)->get()->first();

        if ($check) {
            $newAttributes['last_login'] = Carbon::now();
            $this->model = $this->find($check->id);
            return $this->updateEntity($newAttributes);
        }

        return false;
    }

    public function selectAllExport()
    {
        return $this->model->with(array('clientAddress', 'clientDeliveryAddress', 'clientBillAddress'))->whereNotNull('account_created')->where('active', '=', 1)->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->get();
    }

    public function editAddress($shopId, $clientId, $addressId, $attributes)
    {
        $address = $this->clientAddress->updateByIdAndShopId($shopId, $attributes, $clientId, $addressId);
    }
}
