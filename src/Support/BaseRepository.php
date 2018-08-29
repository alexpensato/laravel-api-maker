<?php

namespace Pensato\Api\Support;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    // Constructor to bind model to repo
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    // Get all instances of model
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Get all instances of model with relations, paginated or not
     *
     * @param array $relations
     * @param int   $skip
     * @param int   $limit
     *
     * @return array
     */
    public function list(array $relations = [], int $skip = 0, int $limit = 0)
    {
        if ($limit > 0) {
            return $this->model->with($relations)->skip($skip)->limit($limit)->get();
        }

        return $this->model->with($relations)->get();
    }

    public function findItem($id, array $relations = [], string $useAsId = null)
    {
        if (empty($useAsId)) {
            return $this->model->with($relations)->find($id);
        }

        return $this->model->with($relations)->where($useAsId, '=', $id)->first();
    }

    // create a new record in the database
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    // update record in the database
    public function update(array $data, $id)
    {
        $record = $this->find($id);
        return $record->update($data);
    }

    // remove record from the database
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    // show the record with the given id
    public function show($id)
    {
        return $this->model-findOrFail($id);
    }

    // Get the associated model
    public function getModel()
    {
        return $this->model;
    }

    // Set the associated model
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    // Get the associated model
    public function unguard()
    {
        $this->model->unguard();
    }
}
