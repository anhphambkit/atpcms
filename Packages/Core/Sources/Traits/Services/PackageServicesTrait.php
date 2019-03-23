<?php
namespace Packages\Core\Sources\Traits\Services;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait PackageServicesTrait
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
        $this->repositories->setModel($model);
    }
    /**
     * Get model by Id
     * @param $id
     * @return Model | null
     */
    public function get($id)
    {
        return $this->repositories->get($id);
    }

    /**
     * Delete model by Id
     * @param $id
     * @return boolean
     */
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $deleted = $this->repositories->delete($id);
            DB::commit();
            return $deleted;
        } catch (\Exception $e){
            DB::rollback();
            activity()->withProperties(['data'  => $id, 'msg' => 'Error when delete '. get_class($this->getModel()). '. ID model: '. $id ])->log($e->getMessage());
            return false;
        }
    }

    /**
     * Update new data to model
     * @param $id
     * @param array $data : List new values to update
     * @return boolean
     */
    public function update($id, $data)
    {
        DB::beginTransaction();
        try {
            $updated = $this->repositories->update($id, $data);
            DB::commit();
            return $updated;
        } catch (\Exception $e) {
            DB::rollback();
            activity()->withProperties(['data'  => 'Delete ID: '.$id, 'msg' => 'Error when delete '. get_class($this->getModel()). '. ID model: '. $id ])->log($e->getMessage());
            return false;
        }
    }

    /**
     * Create new data to model
     * @param array $data : List new values to update
     * @return Model ID
     */
    public function create($data){
        DB::beginTransaction();
        try {
            $created = $this->repositories->create($data);
            DB::commit();
            return $created;
        } catch (\Exception $e) {
            DB::rollback();
            activity()->withProperties(['data'  => $data, 'msg' => 'Error when create '. get_class($this->getModel()). '.'])->log($e->getMessage());
            return false;
        }
    }
    /**
     * Quick create/update
     * @param array $data : List new values to update
     * @param int $id
     * @return mixed
     */
    public function crud($data, $id = null){
        if(is_null($id)){
            return $this->create($data);
        } else {
            return $this->update($id, $data);
        }
    }

    /**
     * Get all
     * @return array
     */
    public function all()
    {
        return $this->repositories->all();
    }

    /**
     * Filter
     * @param array $data
     * @return Collection
     */
    public function filter($data)
    {
        return $this->repositories->filter($data);
    }
}