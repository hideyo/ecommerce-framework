<?php 

namespace Hideyo\Ecommerce\Framework\Services\Client\Entity;

use Hideyo\Ecommerce\Framework\Services\Client\Entity\Client;
use Hideyo\Ecommerce\Framework\Services\Client\Entity\ClientAddressRepository;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;

class ClientRepository extends BaseRepository 
{
    protected $model;

    public function __construct(Client $model, ClientAddressRepository $clientAddress)
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
        return $this->model->where('shop_id', '=', $shopId)->where('email', '=', $email)->get()->first();
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
        return $this->model->with(array('clientAddress', 'clientDeliveryAddress', 'clientBillAddress'))->where('shop_id', '=', $shopId)->where('active', '=', 1)->where('id', '=', $clientId)->first();
    }

    public function selectOneById($clientId)
    {
        return $this->model->with(array('clientAddress', 'clientDeliveryAddress', 'clientBillAddress'))->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->where('active', '=', 1)->where('id', '=', $clientId)->first();
    }

    public function getClientByConfirmationCode($shopId, $email, $confirmationCode)
    {
        return $this->model->where('shop_id', '=', $shopId)->where('email', '=', $email)->where('confirmation_code', '=', $confirmationCode)->get()->first();
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

    public function selectAllExport()
    {
        return $this->model->with(array('clientAddress', 'clientDeliveryAddress', 'clientBillAddress'))->whereNotNull('account_created')->where('active', '=', 1)->where('shop_id', '=', auth('hideyobackend')->user()->selected_shop_id)->get();
    }

    public function editAddress($shopId, $clientId, $addressId, $attributes)
    {
        return $this->clientAddress->updateByIdAndShopId($shopId, $attributes, $clientId, $addressId);
    }
}
