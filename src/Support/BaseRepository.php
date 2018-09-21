<?php

namespace Pensato\Api\Support;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceAbstract;

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
     * Resource key for an item.
     *
     * @var string
     */
    protected $resourceKeySingular = 'data';

    /**
     * Resource key for a collection.
     *
     * @var string
     */
    protected $resourceKeyPlural = 'data';


    /**
     * Constructor to bind model to repo.
     *
     * @param Model $model
     * @param \Pensato\Api\Support\BaseTransformer
     */
    public function __construct(Model $model, $transformer)
    {
        $this->model = $model;

        $this->transformer = $transformer;
    }

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
     * Count items in database
     *
     * @return int
     */
    public function count()
    {
        return $this->model->count();
    }

    /**
     * Get all instances of model with relations, paginated or not
     *
     * @param array $relations
     * @param int   $page
     * @param int   $size
     *
     * @return ResourceAbstract
     */
    public function list(int $page, int $size, array $relations = [])
    {
        $list = null;

        $count = $this->count();

        if ($size > 0) {
            $skip = ($page > 0) ? ($page-1)*$size : 0;
            $count = $count / $size;
            if ($count%$size > 0) {
                $count += 1;
            }
            $list = $this->model->with($relations)->skip($skip)->limit($size)->orderBy($this->defaultOrderBy)->get();

        } elseif ($this->allowFullScan) {
            $list = $this->model->with($relations)->orderBy($this->defaultOrderBy)->get();

        } else {
            $list = $this->model->with($relations)->limit($this->defaultSize)->orderBy($this->defaultOrderBy)->get();
        }

        return $this->loadResourceWithCollection($list, $page, $count);
    }

    /**
     * Find item by id
     *
     * @param $id
     * @param array $relations
     * @param string $useAsId
     *
     * @return ResourceAbstract
     */
    public function findItem($id, array $relations = [], string $useAsId = null)
    {
        $item = null;

        if (empty($useAsId)) {
            $item = $this->model->with($relations)->find($id);
        } else {
            $item = $this->model->with($relations)->where($useAsId, '=', $id)->first();
        }

        return $this->loadResourceWithItem($item);
    }

    /**
     * Create a new record in the database
     *
     * @param array $data
     *
     * @return ResourceAbstract
     */
    public function create(array $data)
    {
        $this->unguardIfNeeded();

        $newData = $this->transformer->mapper($data, $this->model);

        $record = $this->model->create($newData);

        return $this->loadResourceWithItem($record);
    }

    /**
     * Update record in the database
     *
     * @param array $data
     * @param int $id
     *
     * @return ResourceAbstract
     */
    public function update(array $data, $id)
    {
        $this->unguardIfNeeded();

        $record = $this->model->find($id);
        if (!$record) {
            return null;
        }

        $newData = $this->transformer->mapper($data, $record);

        $numberOfRecords = $record->update($newData);
        if($numberOfRecords != 1) {
            return null;
        }

        $item = $this->model->find($id);

        return $this->loadResourceWithItem($item);
    }

    /**
     * Remove record from the database
     *
     * @param int $id
     *
     * @return int
     */
    public function delete($id)
    {
        $item = $this->findItem($id);

        if (!$item) {
            return null;
        }

        return $this->model->destroy($id);
    }

    /**
     * Show the record with the given id
     *
     * @param $id
     *
     * @return ResourceAbstract
     */
    public function show($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Load Fractal Resource with a given item.
     *
     * @param $item
     *
     * @return ResourceAbstract|null
     */
    protected function loadResourceWithItem($item)
    {
        if(!$item) {
            return null;
        }
        /** @var Item $resource */
        $resource = new Item($item, $this->transformer, $this->resourceKeySingular);

        $resource = $this->setMetaIncludes($resource);

        return $resource;
    }

    /**
     * Load Fractal Resource with a given collection.
     *
     * @param $collection
     * @param $page
     * @param $count
     *
     * @return ResourceAbstract|null
     */
    protected function loadResourceWithCollection($collection, $page = 0, $count = 0)
    {
        if(!$collection) {
            return null;
        }
        /** @var Collection $resource */
        $resource = new Collection($collection, $this->transformer, $this->resourceKeyPlural);

        if ($page > 0) {
            $prev = ($page > 1) ? $page-1 : null;
            $next = $page + 1;
            if($next > $count) {
                $next = null;
            }

            // Cursor::__construct($current = null, $prev = null, $next = null, $count = null)
            $cursor = new Cursor($page, $prev, $next, $count);
            $resource->setCursor($cursor);
        }

        $resource = $this->setMetaIncludes($resource);

        return $resource;
    }

    /**
     * Set some meta information.
     *
     * @param Item|Collection $resource
     *
     * @return Collection
     */
    protected function setMetaIncludes($resource)
    {
        $resource->setMetaValue('available_includes', $this->transformer->getAvailableIncludes());
        $resource->setMetaValue('default_includes', $this->transformer->getDefaultIncludes());

        return $resource;
    }

    /**
     * Set the associated model
     *
     * @param Model $model
     *
     * @return RepositoryInterface
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Unguard eloquent model if needed.
     */
    public function unguardIfNeeded()
    {
        if ($this->unguard) {
            $this->model->unguard();
        }
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

