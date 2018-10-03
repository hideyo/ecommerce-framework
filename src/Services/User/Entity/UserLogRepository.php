<?php 

namespace Hideyo\Ecommerce\Framework\Services\User\Entity;
 
use UserLog;
use Hideyo\Ecommerce\Framework\Services\BaseRepository;
 
class UserLogRepository  extends BaseRepository 
{

    protected $model;

    public function __construct(UserLog $model)
    {
        $this->model = $model;
    }
  
    public function create($type, $message, $user_id)
    {
        $this->model->message = $message;
        $this->model->type = $type;
        $this->model->user_id = $user_id;
        $this->model->save();
        
        return $this->model;
    }

    public function updateById(array $attributes, $id)
    {
        $this->model = $this->find($id);
        return $this->updateEntity($attributes);
    }
}