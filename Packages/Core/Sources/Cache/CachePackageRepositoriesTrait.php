<?php
namespace Packages\Core\Sources\Cache;
use Illuminate\Database\Eloquent\Model;

trait CachePackageRepositoriesTrait
{
    protected $model;

    /**
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Model $model
     */
    public function setModel($model)
    {
        $this->model = $model;
        $this->repository->setModel($model);
    }

    /**
     * Get model by Id
     * @param $id
     * @return Model | null
     */
    public function get($id)
    {
        return $this->getDataIfExistCache(__FUNCTION__, func_get_args());
    }

    /**
     * Delete model by Id
     * @param $id
     * @return boolean
     */
    public function delete($id)
    {
        return $this->flushCacheAndUpdateData(__FUNCTION__, func_get_args());
    }

    /**
     * Update new data to model
     * @param $id
     * @param array $data : List new values to update
     * @return boolean
     */
    public function update($id, $data)
    {
        return $this->flushCacheAndUpdateData(__FUNCTION__, func_get_args());
    }

    /**
     * Create new data to model
     * @param array $data : List new values to update
     * @return boolean
     */
    public function create($data)
    {
        return $this->flushCacheAndUpdateData(__FUNCTION__, func_get_args());
    }

    /**
     * List all
     * @return array
     */
    public function all()
    {
        return $this->getDataIfExistCache(__FUNCTION__, func_get_args());
    }

    /**
     * Simple filter model data
     * @param array $data: ['is_active' => true, 'name' => 'ABC']
     */
    public function filter($data){
        return $this->getDataIfExistCache(__FUNCTION__, func_get_args());
    }
}