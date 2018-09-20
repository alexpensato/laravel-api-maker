<?php

namespace Pensato\Api\Support;

use League\Fractal\TransformerAbstract;

abstract class BaseTransformer extends TransformerAbstract
{
    abstract public function mapper($data, $model);
}