<?php

namespace Pensato\Api\Support;

class BaseEnum {

    static function init() {
        $className = get_called_class();
        $class = new \ReflectionClass($className);
        $properties = $class->getStaticProperties();

        $counter = 0;
        foreach ($properties as $name => $value) {
            $className::$$name = new $className($counter);
            $counter++;
        }
    }

    static function fromInt(int $value) {
        $className = get_called_class();
        $class = new \ReflectionClass($className);
        $properties = $class->getStaticProperties();

        if ($value < 0 || $value >= count($properties)) {
            throw new \Exception('Invalid enum property');
        }

        $property = array_keys($properties)[$value];

        return $className::$$property;
    }

    private function __construct($value) {
        $this->value = $value;
    }

    private $value;
}
