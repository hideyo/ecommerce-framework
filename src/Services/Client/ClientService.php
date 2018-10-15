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

    /**
     * The validation rules for the model.
     *
     * @param  integer  $clientId id attribute model    
     * @return array
     */
    private function rules($clientId = false)
    {
        if ($clientId) {
            $rules = array(
                'email' => 'required|email|unique_with:client, shop_id'
            );
        } else {
            $rules = array(
                'email' => 'required|email|unique_with:'.$this->repo->getModel()->getTable().', shop_id',
                'gender' => 'required',
                'firstname' => 'required',
                'lastname' => 'required',
                'street' => 'required',
                'housenumber' => 'required|integer',
                'zipcode' => 'required',
                'city' => 'required',
                'country' => 'required'
            );
        }

        if ($clientId) {
            $rules['email'] =   'required|email|unique_with:'.$this->repo->getModel()->getTable().', shop_id, '.$clientId.' = id';
        }

        return $rules;
    }

    public function create(array $attributes)
    {
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $validator = Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }

        $attributes['password'] = Hash::make($attributes['password']);
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;
        $this->repo->getModel()->fill($attributes);
        $this->repo->getModel()->save();
        $clientAddress = $this->createAddress($attributes, $this->repo->getModel()->id);
        $new['delivery_client_address_id'] = $clientAddress->id;
        $new['bill_client_address_id'] = $clientAddress->id;
        $this->repo->getModel()->fill($new);
        $this->repo->getModel()->save();
        return $this->repo->getModel();
    }

    public function updateById(array $attributes, $clientId)
    {
        $model = $this->find($clientId);
        $attributes['shop_id'] = auth('hideyobackend')->user()->selected_shop_id;
        $attributes['modified_by_user_id'] = auth('hideyobackend')->user()->id;

        $validator = Validator::make($attributes, $this->rules($clientId));

        if ($validator->fails()) {
            return $validator;
        }

        if ($attributes['password']) {
            $attributes['password'] = Hash::make($attributes['password']);
        }

        $model->fill($attributes);
        $model->save();

        return $model;
    }

    public function validateConfirmationCode($confirmationCode, $email, $shopId)
    {
    	return $this->repo->validateConfirmationCodeByConfirmationCodeAndEmail($confirmationCode, $email, $shopId);
	}

    public function validateLogin($attributes) 
    {
        $rules = array(
            'email'            => 'required|email',
            'password'         => 'required|min:2'
        );

        return Validator::make($attributes, $rules);
    }

    public function login($request) 
    {
        $loginData = array(
            'email' => $request->get('email'),
            'password' => $request->get('password'),
            'confirmed' => 1,
            'active' => 1,
            'shop_id' => config()->get('app.shop_id')
        );

        if (auth('web')->attempt($loginData)) {
            return true;
        }
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

    public function validateRegister($attributes) 
    {
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

    public function editAddress($clientId, $addressId, $attributes)
    {
        $clientAddress = $this->repoAddress->find($addressId);

        if($clientAddress) {
            $clientAddress->fill($attributes);
            $clientAddress->save();

            return $clientAddress;
        }

        return false;
    }    

    public function updateByIdAndShopId($shopId, array $attributes, $clientId, $id)
    {
        $this->model = $this->find($id);
        return $this->updateEntity($attributes);
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

    public function selectAllExport() {
        return $this->repo->selectAllExport();
    }

    public function activate($clientId)
    {
        $model = $this->find($clientId);

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

    public function deactivate($clientId)
    {
        $model = $this->find($clientId);

        if ($model) {
            $attributes['confirmed'] = 0;
            $attributes['active'] = 0;
            $attributes['confirmation_code'] = null;
            
            $model->fill($attributes);
            $model->save();
            
            return $model;
        }
        
        return false;
    }

    public function setBillOrDeliveryAddress($shopId, $clientId, $addressId, $type)
    {
        $client = $this->find($clientId);
        
        if ($client) {

            $newAttributes = array();

            if ($type == 'bill') {
                $newAttributes['bill_client_address_id'] = $addressId;
            } elseif ($type == 'delivery') {
                $newAttributes['delivery_client_address_id'] = $addressId;
            }
            
            $client->fill($newAttributes);
            $client->save();
            return $client;
        }
        
        return false;
    }

    public function selectOneByShopIdAndId($shopId, $clientId)
    {
        return $this->repo->selectOneByShopIdAndId($shopId, $clientId);
    }

    public function selectAddressesByClientId($clientId) {
        return $this->repoAddress->selectAllByClientId($clientId);
    }

    public function getAddressModel()
    {
        return $this->repoAddress->getModel();
    }

    public function findAddress($clientAddressId)
    {
        return $this->repoAddress->find($clientAddressId);
    }

    public function validateRegisterNoAccount(array $attributes, $shopId)
    {
        return $this->repo->validateRegisterNoAccount($attributes, $shopId);
    }
}