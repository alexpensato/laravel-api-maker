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

        if ($size > 0) {
            $skip = ($page > 0) ? ($page-1)*$size : 0;
            $list = $this->model->with($relations)->skip($skip)->limit($size)->orderBy($this->defaultOrderBy)->get();

        } elseif ($this->allowFullScan) {
            $list = $this->model->with($relations)->orderBy($this->defaultOrderBy)->get();

        } else {
            $list = $this->model->with($relations)->limit($this->defaultSize)->orderBy($this->defaultOrderBy)->get();
        }

        $count = $this->count();

        return $this->loadResourceWithCollection($list, $page, $count);
    }

    /**
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

    // create a new record in the database
    public function create(array $data)
    {
        $this->unguardIfNeeded();

        $item = $this->model->create($data);

        return $this->loadResourceWithItem($item);
    }

    // update record in the database
    public function update(array $data, $id)
    {
        $this->unguardIfNeeded();

        $record = $this->model->find($id);
        if (!$record) {
            return null;
        }

        $record->fill($data);
        $record->save();

        $item = $record->update($data);

        return $this->loadResourceWithItem($item);
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
     * Load Fractal Resource with a given item.
     *
     * @param $item
     *
     * @return ResourceAbstract
     */
    protected function loadResourceWithItem($item)
    {
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
     * @return ResourceAbstract
     */
    protected function loadResourceWithCollection($collection, $page = 0, $count = 0)
    {
        /** @var Collection $resource */
        $resource = new Collection($collection, $this->transformer, $this->resourceKeyPlural);

        if ($page > 0) {
            $prev = ($page > 1) ? $page-1 : null;
            $next = $page + 1;
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

