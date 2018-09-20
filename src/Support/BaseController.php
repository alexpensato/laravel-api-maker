<?php

namespace Pensato\Api\Support;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as LaravelController;
use Illuminate\Support\Facades\Response;

abstract class BaseController extends LaravelController
{
    /**
     * HTTP header status code.
     *
     * @var int
     */
    const SUCCESS = 200;

    /**
     * HTTP header status code.
     *
     * @var int
     */
    protected $statusCode = self::SUCCESS;

    /**
     * Illuminate\Http\Request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Parameters storage.
     * Belongs to parent class Symfony\Component\HttpFoundation\ParameterBag
     *
     * @var array
     */
    protected $input;

    /**
     * Constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->input = $this->request->all();
    }

    /**
     * Show the form for creating the specified resource.
     *
     * @return Response
     */
    public function create()
    {
        return $this->errorNotImplemented();
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return mixed
     */
    public function edit($id)
    {
        return $this->errorNotImplemented();
    }

    /**
     * Getter for statusCode.
     *
     * @return int
     */
    protected function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Setter for statusCode.
     *
     * @param int $statusCode Value to set
     *
     * @return self
     */
    protected function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Get the validation rules for create.
     *
     * @return array
     */
    protected function rulesForCreate()
    {
        return [];
    }

    /**
     * Get the validation rules for update.
     *
     * @param int $id
     *
     * @return array
     */
    protected function rulesForUpdate($id)
    {
        return [];
    }

    /**
     * Respond with a given array of items.
     *
     * @param array $array
     * @param array $headers
     *
     * @return mixed
     */
    protected function respondWithArray(array $array, array $headers = [])
    {
        return response()->json($array, $this->statusCode, $headers);
    }

    /**
     * Response with the current error.
     *
     * @param string $message
     *
     * @return mixed
     */
    protected function respondWithError($message)
    {
        return $this->respondWithArray([
            'error' => [
                'http_code' => $this->statusCode,
                'message'   => $message,
            ],
        ]);
    }

    /**
     * Generate a Response with a 403 HTTP header and a given message.
     *
     * @param $message
     *
     * @return Response
     */
    protected function errorForbidden($message = 'Forbidden')
    {
        return $this->setStatusCode(403)->respondWithError($message);
    }

    /**
     * Generate a Response with a 500 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return Response
     */
    protected function errorInternalError($message = 'Internal Error')
    {
        return $this->setStatusCode(500)->respondWithError($message);
    }

    /**
     * Generate a Response with a 404 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return Response
     */
    protected function errorNotFound($message = 'Resource Not Found')
    {
        return $this->setStatusCode(404)->respondWithError($message);
    }

    /**
     * Generate a Response with a 401 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return Response
     */
    protected function errorUnauthorized($message = 'Unauthorized')
    {
        return $this->setStatusCode(401)->respondWithError($message);
    }

    /**
     * Generate a Response with a 400 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return Response
     */
    protected function errorWrongArgs($message = 'Wrong Arguments')
    {
        return $this->setStatusCode(400)->respondWithError($message);
    }

    /**
     * Generate a Response with a 501 HTTP header and a given message.
     *
     * @param string $message
     *
     * @return Response
     */
    protected function errorNotImplemented($message = 'Not implemented')
    {
        return $this->setStatusCode(501)->respondWithError($message);
    }


    /**
     * Specify relations for eager loading.
     *
     * @return array
     */
    protected function getEagerLoad()
    {
        $include = camel_case($this->request->input('include', ''));
        $includes = explode(',', $include);
        $includes = array_filter($includes);

        return $includes ?: [];
    }

}