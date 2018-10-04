<?php

namespace Hideyo\Ecommerce\Framework\Services\Client;

use Validator;
use File;
use Hideyo\Ecommerce\Framework\Services\Client\Entity\ClientRepository;
use Hideyo\Ecommerce\Framework\Services\Client\Entity\ClientAddressRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
use Carbon\Carbon; 
use Hash;

class ClientService extends BaseService
{
	public function __construct(ClientRepository $client, ClientAddressRepository $clientAddress)
	{
		$this->repo = $client;
		$this->repoAddress = $clientAddress;
	} 

    public function validateConfirmationCodeByConfirmationCodeAndEmail($confirmationCode, $email, $shopId)
    {
    	return $this->repo->validateConfirmationCodeByConfirmationCodeAndEmail($confirmationCode, $email, $shopId);
	}

    public function validateLogin($attributes) 
    {
        $rules = array(
            'email'            => 'required|email',
            'password'         => 'required'
        );

        return Validator::make($attributes, $rules);
    }

    public function confirmClient($confirmationCode, $email, $shopId)
    {
        $model = $this->repo->getClientByConfirmationCode($shopId, $email, $confirmationCode);

        if ($model) {
            $attributes['confirmed'] = 1;
            $attributes['active'] = 1;
            $attributes['confirmation_code'] = null;
            
            $model->fill($attributes);
            $model->save();
            return $model;
        }
        
        return false;
    }

    public function getConfirmationCodeByEmail($email, $shopId)
    {
        $model = $this->repo->checkEmailByShopIdAndNoAccountCreated($email, $shopId);

        if ($model) {
            $attributes['confirmation_code'] = md5(uniqid(mt_rand(), true));
            $model->fill($attributes);
            $model->save();

            return $model;
        }
        
        return false;
    }

    public function validateRegister($attributes) {

        // create the validation rules ------------------------
        $rules = array(
            'email'         => 'required|email',     // required and must be unique in the ducks table
            'password'      => 'required',
            'firstname'     => 'required',
            'lastname'      => 'required',
            'zipcode'       => 'required',
            'housenumber'   => 'required|numeric',
            'houseletter'   => 'alpha',
            'street'        => 'required',
            'city'          => 'required',
            'country'       => 'required'
        );

        return $validator = Validator::make($attributes, $rules);

    }


    public function register($attributes, $shopId, $accountConfirmed = false)
    {
        $client = $this->repo->checkEmailByShopId($attributes['email'], $shopId);

        if($client AND $client->account_created) {
        	return false;
        }

        if($client) {
        	$model = $client;
        } else {
        	$model = $this->repo->getModel();
        }

        $attributes['shop_id'] = $shopId;
        $attributes['modified_by_user_id'] = null;
        $attributes['active'] = 0;
        $attributes['confirmed'] = 0;
        $attributes['confirmation_code'] = md5(uniqid(mt_rand(), true));      
        if($accountConfirmed) {
            $attributes['active'] = 1;
            $attributes['confirmed'] = 1;
            $attributes['confirmation_code'] = null;
        }
        
        if (isset($attributes['password'])) {            
            $attributes['password'] = Hash::make($attributes['password']);
            $attributes['account_created'] = Carbon::now()->toDateTimeString();
        }

        $model->fill($attributes);
        $model->save();

        $clientAddress = $this->createAddress($attributes, $model->id);
        $new['delivery_client_address_id'] = $clientAddress->id;
        $new['bill_client_address_id'] = $clientAddress->id;
        $model->fill($new);
        $model->save();
        return $model;
    }

    public function createAddress($attributes, $clientId) 
    {
        $attributes['client_id'] = $clientId;
  		$model = $this->repoAddress->getModel();
        $model->fill($attributes);
        $model->save();
        
        return $model;
    }

}