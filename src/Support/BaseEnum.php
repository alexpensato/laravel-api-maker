<?php

namespace Pensato\Api\Support;

class BaseEnum {

	static function init() {
		$className = get_called_class();
		try {
            $class = new \ReflectionClass($className);
            $properties = $class->getStaticProperties();

        } catch (\ReflectionException $e) {
            return;
        }

		$counter = 0;

		foreach ($properties as $name => $value) {
			$className::$$name = new $className($counter);
			$counter++;
		}
	}

	static function fromInt(int $value) {
        $className = get_called_class();
        try {
            $class = new \ReflectionClass($className);
            $properties = $class->getStaticProperties();

        } catch (\ReflectionException $e) {
            return null;
        }

		if ($value < 0 || $value >= count($properties)) {
			return null;
		}
		
		$property = array_keys($properties)[$value];

		return $className::$$property;
	}
	
	private function __construct($value) {
		$this->value = $value;
	}

	private $value;
}
