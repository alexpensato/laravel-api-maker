<?php

namespace Pensato\Api\Support;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;

abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * Fractal Transformer instance.
     *
     * @var \League\Fractal\TransformerAbstract
     */
    protected $transformer;

    /**
     * Do we need to unguard the model before create/update?
     *
     * @var bool
     */
    protected $unguard = false;

    /**
     * Default size for pagination
     *
     * @var int
     */
    protected $defaultSize = 10;

    /**
     * Maximum size that can be set via $_GET['size'].
     *
     * @var int
     */
    protected $maxSize = 50;

    /**
     * Default order by for queries
     *
     * @var string
     */
    protected $defaultOrderBy = 'id';

    /**
     * Whether a request should be allowed to perform a full scan on a repository
     *
     * @var boolean
     */
    protected $allowFullScan = false;


    /**
     * Constructor to bind model to repo.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;

        $this->transformer = $this->transformer();
    }

    /**
     * Transformer for the current model.
     *
     * @return \League\Fractal\TransformerAbstract
     */
    abstract protected function transformer();


    /**
     * Get all instances of model with relations
     *
     * @return array
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Get all instances of model with relations, paginated or not
     *
     * @param array $relations
     * @param int   $page
     * @param int   $size
     *
     * @return array
     */
    public function list(int $page, int $size, array $relations = [])
    {
        if ($size > 0) {
            $skip = ($page > 0) ? ($page-1)*$size : 0;
            return $this->model->with($relations)->skip($skip)->limit($size)->orderBy($this->defaultOrderBy)->get();
        }

        if ($this->allowFullScan) {
            return $this->model->with($relations)->orderBy($this->defaultOrderBy)->get();
        }

        return $this->model->with($relations)->limit($this->defaultSize)->orderBy($this->defaultOrderBy)->get();
    }

    public function count(array $relations = [])
    {
        return $this->model->with($relations)->count();
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
        // saveOrFail
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
        return $this->model->findOrFail($id);
    }

    /**
     * Respond with a given item.
     *
     * @param $item
     *
     * @return mixed
     */
    protected function respondWithItem($item)
    {
        $resource = new Item($item, $this->transformer, $this->resourceKeySingular);

        $rootScope = $this->prepareRootScope($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    /**
     * Respond with a given collection.
     *
     * @param $collection
     * @param $page
     * @param $count
     *
     * @return mixed
     */
    protected function respondWithCollection($collection, $page = 0, $count = 0)
    {
        $resource = new Collection($collection, $this->transformer, $this->resourceKeyPlural);

        if ($page > 0) {
            $prev = ($page > 1) ? $page-1 : null;
            $next = $page + 1;
            // Cursor::__construct($current = null, $prev = null, $next = null, $count = null)
            $cursor = new Cursor($page, $prev, $next, $count);
            $resource->setCursor($cursor);
        }

        $rootScope = $this->prepareRootScope($resource);

        return $this->respondWithArray($rootScope->toArray());
    }

    /**
     * Prepare root scope and set some meta information.
     *
     * @param Item|Collection $resource
     *
     * @return \League\Fractal\Scope
     */
    protected function prepareRootScope($resource)
    {
        $resource->setMetaValue('available_includes', $this->transformer->getAvailableIncludes());
        $resource->setMetaValue('default_includes', $this->transformer->getDefaultIncludes());

        return $this->fractal->createData($resource);
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

    /**
     * Calculates size of number of items displayed in list.
     *
     * @param int $size
     *
     * @return int
     */
    protected function calculateSize($size)
    {
        if ($size.isEmpty()) {
            $size = $this->defaultSize;
        }

        return ($this->maxSize < $size) ? $this->maxSize : $size;
    }
}

