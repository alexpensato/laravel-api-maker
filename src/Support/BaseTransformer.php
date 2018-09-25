<?php

namespace Pensato\Api\Support;

use League\Fractal\TransformerAbstract;

abstract class BaseTransformer extends TransformerAbstract
{
    protected $volatileFields = [];

    abstract public function mapper($data, $model);

    /**
     * @return array
     */
    public function getVolatileFields(): array
    {
        return $this->volatileFields;
    }

    /**
     * @param array $volatileFields
     */
    public function setVolatileFields(array $volatileFields)
    {
        $this->volatileFields = $volatileFields;
    }
}