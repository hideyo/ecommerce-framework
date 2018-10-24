<?php
namespace Hideyo\Ecommerce\Framework\Services;

 class BaseRepository 
{
    /**
     * Select all model items
     * @return object|null
     */
	public function selectAll()
	{
	    return $this->model->where('shop_id', auth('hideyobackend')->user()->selected_shop_id)->get();
	}

    /**
     * select all models by shop id
     * @param  string $shopId 
     * @return object         
     */
    public function selectAllByShopId($shopId)
    {
         return $this->model->where('shop_id', $shopId)->get();
    }

    /**
     * select all active models by shop id
     * @param  string $shopId 
     * @return object         
     */
    public function selectAllActiveByShopId($shopId)
    {
         return $this->model->where('shop_id', $shopId)->where('active', 1)->get();
    }

    /**
     * Get model
     * @return return object
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * Find a model item
     * @param  integer $id
     * @return object|null
     */
    public function find($modelId)
    {
        return $this->model->find($modelId);
    }

    /**
     * update a model
     * @param  array  $attributes 
     * @return object             
     */
    public function updateEntity(array $attributes = array())
    {
        if (count($attributes) > 0) {
            $this->model->fill($attributes);
            $this->model->save();
        }

        return $this->model;
    }

    /**
     * destroy model
     * @param  integer $id 
     * @return object     
     */
    public function destroy($modelId)
    {
        $this->model = $this->find($modelId);
        $this->model->save();
        return $this->model->delete();
    }
}