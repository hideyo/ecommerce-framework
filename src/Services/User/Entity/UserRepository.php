<?php 

namespace Hideyo\Ecommerce\Framework\Services\User\Entity;
 
use Hideyo\Ecommerce\Framework\Services\User\Entity\User;
use Hash;
use Validator;
use Mail;
use Lang;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;

class UserRepository  extends BaseRepository 
{
    protected $model;
    protected $validator;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function getValidator()
    {
        return $this->validator;
    }



}