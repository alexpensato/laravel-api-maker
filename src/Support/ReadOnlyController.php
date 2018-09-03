<?php

namespace Pensato\Api\Support;

use Illuminate\Support\Facades\Response;

abstract class ReadOnlyController extends ApiController
{
    /**
     * Read-only controller cannot store objects
     */
    public function store()
    {
        return $this->errorNotImplemented();
    }

    /**
     * Read-only controller cannot update objects
     *
     * @param int $id
     *
     * @return
     */
    public function update($id)
    {
        return $this->errorNotImplemented();
    }

    /**
     * Read-only controller cannot delete objects
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        return $this->errorNotImplemented();
    }

    /**
     * Read-only controller cannot create objects
     *
     * @return
     */
    protected function rulesForCreate()
    {
        return $this->errorNotImplemented();
    }

    /**
     * Read-only controller cannot update objects
     *
     * @param int $id
     *
     * @return
     */
    protected function rulesForUpdate($id)
    {
        return $this->errorNotImplemented();
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

}