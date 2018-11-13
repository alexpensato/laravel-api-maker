<?php

namespace Pensato\Api\Support;

class SoftDeletesPolicyEnum extends BaseEnum
{
    static $NONE_TRASHED, $ONLY_TRASHED, $WITH_TRASHED;
}

SoftDeletesPolicyEnum::init();