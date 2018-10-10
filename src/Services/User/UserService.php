<?php

namespace Hideyo\Ecommerce\Framework\Services\User;

use Validator;
use Hash;
use Hideyo\Ecommerce\Framework\Services\User\Entity\UserRepository;
use Hideyo\Ecommerce\Framework\Services\BaseService;
 
class UserService extends BaseService
{
	public function __construct(UserRepository $user)
	{
		$this->repo = $user;
	} 

    /**
     * The validation rules for the model.
     *
     * @param  integer  $userId id attribute model    
     * @return array
     */
    private function rules($userId = false, $attributes = false)
    {
        $rules = array(
            'email'         => 'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable(),
            'username'      => 'required|between:4,65|unique_with:'.$this->repo->getModel()->getTable()
        );
        
        if ($userId) {
            $rules['email']     =   $rules['email'].', '.$userId.' = id';
            $rules['username']  =   $rules['email'].', '.$userId.' = id';
        }

        return $rules;
    }


    public function updateById(array $attributes, $avatar, $userId)
    {

        $validator = Validator::make($attributes, $this->rules($userId));

        if ($validator->fails()) {
            return $validator;
        }

        $model = $this->find($userId);
        $attributes['password']= Hash::make(array_get($attributes, 'password'));
        return $this->updateOrAddModel($model, $attributes);
    }



    /**
     * Signup a new account with the given parameters
     *
     * @param  array $input Array containing 'username', 'email' and 'password'.
     *
     * @return  User User object that may or may not be saved successfully. Check the id to make sure.
     */
    public function signup($attributes)
    {
        $validator = Validator::make($attributes, $this->rules());

        if ($validator->fails()) {
            return $validator;
        }


        $attributes['password'] = Hash::make(array_get($attributes, 'password'));
        $attributes['confirmed'] = 0;

        $attributes['confirmation_code']     = md5(uniqid(mt_rand(), true));

        $model =  $this->updateOrAddModel($this->repo->getModel(), $attributes);

        if ($model) {

            if (config()->get('confide::signup_email')) {
                $user = $model;
                Mail::queueOn(
                    config()->get('confide::email_queue'),
                    config()->get('confide::email_account_confirmation'),
                    compact('user'),
                    function ($message) use ($user) {
                        $message
                            ->to($user->email, $user->username)
                            ->subject(Lang::get('confide::confide.email.account_confirmation.subject'));
                    }
                );
            }
        }
  
        return $model;
    }

    public function updateProfileById(array $attributes, $avatar, $userId)
    {
        $this->model = $this->find($userId);
        if ($this->validator->validate($this->model, 'update')) {
            return $this->updateProfileEntity($attributes, $avatar);
        }

        return false;
    }

    public function updateShopProfileById($shop, $userId)
    {
        $this->model = $this->find($userId);

        if ($this->model->company_id == $shop->company_id) {
            $this->model->selected_shop_id = $shop->id;
            $this->model->save();
        }

        return true;
    }

    public function updateProfileEntity(array $attributes = array(), $avatar)
    {
        if (count($attributes) > 0) {
            $this->model->username = array_get($attributes, 'username');
  
            $this->model->selected_shop_id    = array_get($attributes, 'selected_shop_id');
            $this->model->email    = array_get($attributes, 'email');
            $this->model->language_id    = array_get($attributes, 'language_id');
            $this->model->save();

            return $this->model;
        }
    }

    /**
     * Attempts to login with the given credentials.
     *
     * @param  array $input Array containing the credentials (email/username and password)
     *
     * @return  boolean Success?
     */
    public function login($input)
    {
        if (! isset($input['password'])) {
            $input['password'] = null;
        }

        return \Confide::logAttempt($input, config()->get('confide::signup_confirm'));
    }

    /**
     * Checks if the credentials has been throttled by too
     * much failed login attempts
     *
     * @param  array $credentials Array containing the credentials (email/username and password)
     *
     * @return  boolean Is throttled
     */
    public function isThrottled($input)
    {
        return \Confide::isThrottled($input);
    }

    /**
     * Checks if the given credentials correponds to a user that exists but
     * is not confirmed
     *
     * @param  array $credentials Array containing the credentials (email/username and password)
     *
     * @return  boolean Exists and is not confirmed?
     */
    public function existsButNotConfirmed($input)
    {
        $user = \Confide::getUserByEmailOrUsername($input);

        if ($user) {
            $correctPassword = Hash::check(
                isset($input['password']) ? $input['password'] : false,
                $user->password
            );

            return (! $user->confirmed && $correctPassword);
        }
    }

    /**
     * Resets a password of a user. The $input['token'] will tell which user.
     *
     * @param  array $input Array containing 'token', 'password' and 'password_confirmation' keys.
     *
     * @return  boolean Success
     */
    public function resetPassword($input)
    {
        $result = false;
        $user   = Confide::userByResetPasswordToken($input['token']);

        if ($user) {
            $user->password              = $input['password'];
            $user->password_confirmation = $input['password_confirmation'];
            $result = $this->save($user);
        }

        // If result is positive, destroy token
        if ($result) {
            Confide::destroyForgotPasswordToken($input['token']);
        }

        return $result;
    }


}