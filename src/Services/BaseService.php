<?php

namespace Hideyo\Ecommerce\Framework\Services;
use Notification;
  
class BaseService
{
    /**
     * Find a model item
     * @param  integer $id
     * @return object|null
     */
    public function find($id)
    {
        return $this->repo->find($id);
    }

    /**
     * Select all model items
     * @return object|null
     */
    public function selectAll()
    {
        return $this->repo->selectAll();
    }

    /**
     * Get model
     * @return return object
     */
    public function getModel()
    {
        return $this->repo->getModel();
    }

    /**
     * update or add model
     * @param  object $model      
     * @param  array $attributes 
     * @return object             
     */
    public function updateOrAddModel($model, $attributes) 
    {
        if (count($attributes) > 0) {
            $model->fill($attributes);
            $model->save();
        }
        return $model;  
    }

    /**
     * destroy model
     * @param  integer $id 
     * @return object     
     */
    public function destroy($id)
    {
        $model = $this->find($id);
        return $model->delete();
    }

    /**
     * Notifications and redirect
     * @param  string $routeName  
     * @param  object $result     
     * @param  string $successMsg 
     * @return mixed          
     */
    public function notificationRedirect($routeName, $result, $successMsg) 
    {
        if (isset($result->id)) {
            Notification::success($successMsg);
            if(is_array($routeName)) {
                return redirect()->route($routeName[0], $routeName[1]);
            }
            return redirect()->route($routeName);   
        }
        
        foreach ($result->errors()->all() as $error) {
            Notification::error($error);
        }

        return redirect()->back()->withInput();
    }
}