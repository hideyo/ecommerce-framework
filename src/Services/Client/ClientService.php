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

    public function validateConfirmationCode($confirmationCode, $email, $shopId)
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

    public function requestChangeAccountDetails($attributes, $shopId) {

        $client = $this->repo->checkEmailByShopId($attributes['email'], $shopId);

        if ($client) {
            $newAttributes = array(
                'new_email' => $attributes['email'],
                'new_password' => Hash::make($attributes['password']),
                'confirmation_code' => md5(uniqid(mt_rand(), true))
            );

            $client->fill($newAttributes);
            $client->save();
            
            return $client;
        }

        return false;
    }

    public function changeAccountDetails($confirmationCode, $newEmail, $shopId) {

        $client = $this->repo->validateConfirmationCodeByConfirmationCodeAndNewEmail($confirmationCode, $newEmail, $shopId);

        if ($client) {
            $newAttributes['email'] = $client->new_email;
            $newAttributes['password'] = $client->new_password;
            $newAttributes['confirmed'] = 1;
            $newAttributes['active'] = 1;
            $newAttributes['confirmation_code'] = null;
            $newAttributes['new_email'] = null;
            $newAttributes['new_password'] = null;
            $client->fill($newAttributes);
            $client->save();
            
            return $client;

        }

        return false;

    }


    public function changePassword(array $attributes, $shopId)
    {
        $result = array();
        $result['result'] = false;

        $client = $this->repo->validateConfirmationCodeByConfirmationCodeAndEmail($attributes['confirmation_code'], $attributes['email'], $shopId);

        if ($client) {
            if ($attributes['password']) {
                $newAttributes['confirmed'] = 1;
                $newAttributes['active'] = 1;
                $newAttributes['confirmation_code'] = null;
                $newAttributes['password'] = Hash::make($attributes['password']);
                $client->fill($newAttributes);
                $client->save();
                return $client;
            }
        }

        return false;
    }




}