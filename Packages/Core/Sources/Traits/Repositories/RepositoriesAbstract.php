<?php
namespace Packages\Core\Sources\Traits\Repositories;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

trait RepositoriesAbstract
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
    }

    /**
     * Get model by Id
     * @param $id
     * @return Model | null
     */
    public function get($id)
    {
        return $this->model->find($id);
    }
    
    /**
     * Delete model by Id
     * @param $id
     * @return boolean
     */
    public function delete($id)
    {
        return $this->get($id)->delete();
    }

    /**
     * Update new data to model
     * @param $id
     * @param array $data : List new values to update
     * @return boolean
     */
    public function update($id, $data)
    {
        return $this->model->find($id)->update($data);
    }

    /**
     * Create new data to model
     * @param array $data : List new values to update
     * @return boolean
     */
    public function create($data)
    {
        return $this->getModel()->create($data);
    }

    /**
     * List all
     * @return array
     */
    public function all()
    {
        return $this->getModel()->all();
    }

    /**
     * Simple filter model data
     * @param array $data: ['is_active' => true, 'name' => 'ABC']
     */
    public function filter($data){
        return $this->getModel()->where($data);
    }
}