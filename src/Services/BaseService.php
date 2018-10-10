<?php

namespace Hideyo\Ecommerce\Framework\Services;
use Notification;
 
class BaseService
{
    public function find($id)
    {
        return $this->repo->find($id);
    }

    public function selectAll()
    {
        return $this->repo->selectAll();
    }

    public function getModel()
    {
        return $this->repo->getModel();
    }

    public function updateOrAddModel($model, $attributes) 
    {
        if (count($attributes) > 0) {
            $model->fill($attributes);
            $model->save();
        }
        return $model;  

    }

    public function destroy($id)
    {
        $model = $this->find($id);
        return $model->delete();
    }


    public function notificationRedirect($routeName, $result, $successMsg) {

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