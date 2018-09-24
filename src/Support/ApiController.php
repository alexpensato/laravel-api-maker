<?php

namespace Pensato\Api\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\DataArraySerializer;

abstract class ApiController extends BaseController
{
    /**
     * Fractal Manager instance.
     *
     * @var Manager
     */
    protected $fractal;

    /**
     * Interface for Repository binding
     *
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * Resource key for an item.
     *
     * @var string
     */
    protected $resourceKeySingular = 'data';

    /**
     * Aditional meta data added to response
     *
     * @var array
     */
    protected $metaInfo;

    /**
     * Message to be returned after successful delete request
     *
     * @var array
     */
    protected $deleteMessage = ['message' => 'Deleted'];

    /**
     * Constructor.
     *
     * @param Request $request
     * @param RepositoryInterface $repositoryInterface
     * @param array
     */
    public function __construct(Request $request, RepositoryInterface $repositoryInterface, $metaInfo)
    {
        parent::__construct($request);

        $this->repository = $repositoryInterface;

        $this->metaInfo = $metaInfo;

        $this->fractal = new Manager();
        $this->fractal->setSerializer($this->serializer());

        if ($this->request->has('include')) {
            $this->fractal->parseIncludes(camel_case($this->request->input('include')));
        }
    }

    /**
     * Serializer for the current model.
     *
     * @return \League\Fractal\Serializer\SerializerAbstract
     */
    protected function serializer()
    {
        return new DataArraySerializer();
    }

    /**
     * Display a listing of the resource.
     * GET /api/v1/{resource}.
     *
     * @param array $metaInfo
     *
     * @return Response
     */
    public function indexWithMetaResponse($metaInfo)
    {
        $relations = $this->getEagerLoad();
        $page = (int) $this->request->input('page');
        $size = (int) $this->request->input('size');

        $resource = $this->repository->list($page, $size, $relations);

        $resource = $this->addMetaIncludes($resource, $metaInfo);

        $scope = $this->fractal->createData($resource);

        return $this->respondWithArray($scope->toArray());
    }

    /**
     * Store a newly created resource in repository.
     * POST /api/v1/{resource}.
     *
     * @param array $metaInfo
     *
     * @return Response
     */
    public function storeWithMetaResponse($metaInfo)
    {
        $data = $this->request->json()->get($this->resourceKeySingular);

        if (!$data) {
            return $this->errorWrongArgs('Empty data');
        }

        /** @var \Illuminate\Contracts\Validation\Validator $validator */
        $validator = Validator::make($data, $this->rulesForCreate());
        if ($validator->fails()) {
            return $this->errorWrongArgs($validator->errors()->messages());
        }

        $item = $this->repository->create($data);
        if (!$item) {
            return $this->errorInternalError('Error saving resource to repository.');
        }

        $item = $this->addMetaIncludes($item, $metaInfo);

        $scope = $this->fractal->createData($item);

        return $this->respondWithArray($scope->toArray());
    }

    /**
     * Display the specified resource.
     * GET /api/v1/{resource}/{id}.
     *
     * @param int $id
     * @param array $metaInfo
     *
     * @return Response
     */
    public function showWithMetaResponse($id, $metaInfo)
    {
        $relations = $this->getEagerLoad();

        $item = $this->findItem($id, $relations);

        if (!$item) {
            return $this->errorNotFound();
        }

        $item = $this->addMetaIncludes($item, $metaInfo);

        $scope = $this->fractal->createData($item);

        return $this->respondWithArray($scope->toArray());
    }

    /**
     * Display the count of the specified resource.
     * GET /api/v1/{resource}/count.
     *
     * @return Response
     */
    public function count()
    {
        $count = $this->repository->count();

        if (!$count) {
            return $this->errorNotFound();
        }

        return $count;
    }

    /**
     * Update the specified resource in repository.
     * PUT /api/{resource}/{id}.
     *
     * @param int $id
     * @param array $metaInfo
     *
     * @return Response
     */
    public function updateWithMetaResponse($id, $metaInfo)
    {
        $data = $this->request->json()->get($this->resourceKeySingular);

        if (!$data) {
            return $this->errorWrongArgs('Empty data');
        }

        $validator = Validator::make($data, $this->rulesForUpdate($id));
        if ($validator->fails()) {
            return $this->errorWrongArgs($validator->errors()->messages());
        }

        $item = $this->repository->update($data, $id);
        if (!$item) {
            return $this->errorNotFound();
        }

        $item = $this->addMetaIncludes($item, $metaInfo);

        $scope = $this->fractal->createData($item);

        return $this->respondWithArray($scope->toArray());
    }

    /**
     * Remove the specified resource from repository.
     * DELETE /api/{resource}/{id}.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $count = $this->repository->delete($id);

        if (!$count || $count === 0) {
            return $this->errorNotFound();
        }

        return response()->json($this->deleteMessage);
    }


    /**
     * Get item according to mode.
     *
     * @param int   $id
     * @param array $relations
     *
     * @return mixed
     */
    protected function findItem($id, $relations = [])
    {
        if ($this->request->has('use_as_id')) {
            return $this->repository->findItem($id, $relations, $this->request->input('use_as_id'));
        }

        return $this->repository->findItem($id, $relations);
    }

    /**
     * Prepare root scope
     *
     * @param Item|Collection $resource
     *
     * @return \League\Fractal\Scope
     */
    protected function getScope($resource)
    {
        return $this->fractal->createData($resource);
    }

    /**
     * Add meta information.
     *
     * @param Item|Collection $resource
     * @param array $metaInfo
     *
     * @return Collection
     */
    protected function addMetaIncludes($resource, $metaInfo)
    {
        foreach ($metaInfo as $key => $value) {
            $resource->setMetaValue($key, $value);
        }

        return $resource;
    }


}