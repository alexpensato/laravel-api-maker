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
     * @param array $volatileFields
     * @param SoftDeletesPolicyEnum $softDeletesPolicy
     *
     * @return Response
     */
    public function indexWithMetaResponse($metaInfo, $volatileFields = [], SoftDeletesPolicyEnum $softDeletesPolicy = null)
    {
        $relations = $this->getEagerLoad();
        $page = (int) $this->request->input('page');
        $size = (int) $this->request->input('size');
        $filters = (array) $this->request->input('filter');
        $orderBy = (array) $this->request->input('order_by');

        if (empty($volatileFields)) {
            $strVolatile = (String) $this->request->input('volatileFields');
            if(! empty($strVolatile)) {
                $volatileFields = explode(',', $strVolatile);
            }
        }

        $resource = $this->repository->list($page, $size, $relations, $volatileFields, $filters, $orderBy, $softDeletesPolicy);

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
     * Associate a list of resources provided in the request body with another resource they belong to
     * PATCH /api/v1/{resource}
     *
     * @return Response
     */
    public function associate()
    {
        $str = $this->request->query('belongs_to');

        if ($str) {
            return $this->associateHasMany($str);
        }

        $str = $this->request->query('attach');

        if ($str) {
            return $this->attachManyToMany($str);
        }

        $str = $this->request->query('detach');

        if ($str) {
            return $this->attachManyToMany($str, 'detach');
        }

        return $this->errorWrongArgs("Missing attach, detach or belongs_to parameters in the query string.");
    }

    /**
     * Associate using OneToMany relation
     * Query string: ?belongs_to={main_model_name}&model={relation_model_name}&field={field_name}
     *
     * @param string $strParam
     *
     * @return Response
     */
    protected function associateHasMany($strParam)
    {
        // Build repository name
        $objectName = str_replace('_', '', ucwords($strParam, '_'));
        $repositoryFullName = get_class($this->repository);
        $exploded = explode('\\',$repositoryFullName);
        $repositoryName = end($exploded);
        $repositoryName = str_replace($repositoryName,$objectName . 'Repository', $repositoryFullName);

        // Get json object data
        $objectName = lcfirst($objectName);
        $objectData = $this->request->json()->get($objectName);
        if (!$objectData) {
            return $this->errorWrongArgs("Parameter '" . $objectName . "' not found in the request body.");
        }
        if (!isset($objectData['id'])) {
            return $this->errorWrongArgs("Parameter 'id' not found in the request body.");
        }

        // Get field to be mapped to relation foreign key
        $fieldName = $this->request->query('field');
        if (empty($fieldName)) {
            $fieldName = $objectName . "_id";
        }

        // Get json name parameter
        $jsonParam = $this->request->query('model');
        if (empty($jsonParam) || !isset($objectData[$jsonParam])) {
            $uri = $this->request->path();
            $exploded = explode('/', $uri);
            $routeName = end($exploded);
            if (!isset($objectData[$routeName])) {
                return $this->errorWrongArgs("Query string parameter 'model' is invalid or '" . $routeName . "' not found in the request body.");
            }
            $jsonParam = $routeName;
        }

        $response = $this->repository->associate($repositoryName, $objectData['id'], $objectData[$jsonParam], $fieldName);

        if (is_string($response)) {
            return $this->errorWrongArgs($response);
        }

        return response()->json(['updated_resources' => $response]);
    }

    /**
     * Associate using ManyToMany relation
     * Query string: ?attach={model_name} or ?detach={model_name}
     *
     * @param string $str
     * @param string $type
     *
     * @return Response
     */
    protected function attachManyToMany($strRelation, $type = 'attach')
    {
        $uri = $this->request->path();
        $exploded = explode('/', $uri);
        $routeName = end($exploded);
        $objectName = str_replace('_', '', ucwords($routeName, '_'));

        $objectName = lcfirst($objectName);
        $objectArray = $this->request->json()->get($objectName);
        if (!$objectArray) {
            return $this->errorWrongArgs("Array parameter '" . $objectName . "' not found in the request body.");
        }

        foreach ($objectArray as $objectData) {
            if (!$objectData['id']) {
                return $this->errorWrongArgs("Parameter 'id' not found in the request body.");
            }
            if (!$objectData[$strRelation]) {
                return $this->errorWrongArgs("Parameter '" . $strRelation . "' not found in the request body.");
            }
        }

        $relation = str_replace('_', '', ucwords($strRelation, '_'));

        $commited = array();
        $errors = array();
        foreach ($objectArray as $objectData) {
            if($type == 'detach') {
                $response = $this->repository->detach($relation, $objectData['id'], $objectData[$strRelation]);
            } else {
                $response = $this->repository->attach($relation, $objectData['id'], $objectData[$strRelation]);
            }

            if($response) {
                $commited[] = $objectData['id'];
            } else {
                $errors[] = $objectData['id'];
            }
        }

        return response()->json([$type.'ed_resources' => $commited, 'errors' => $errors]);
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