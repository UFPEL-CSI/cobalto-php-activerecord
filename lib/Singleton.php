<?php

namespace ActiveRecord;

/**
 * This implementation of the singleton pattern does not conform to the strong definition
 * given by the "Gang of Four." The __construct() method has not be privatized so that
 * a singleton pattern is capable of being achieved; however, multiple instantiations are also
 * possible. This allows the user more freedom with this pattern.
 */
abstract class Singleton
{
	/**
	 * Array of cached singleton objects.
	 *
	 * @var array
	 */
	private static $instances = [];

	/**
	 * Static method for instantiating a singleton object.
	 *
	 * @return object
	 */
	final public static function instance()
	{
		$class_name = get_called_class();

		if (!isset(self::$instances[$class_name])) {
			self::$instances[$class_name] = new $class_name();
		}

		return self::$instances[$class_name];
	}

	/**
	 * Similar to a get_called_class() for a child class to invoke.
	 *
	 * @return string
	 */
	final protected function get_called_class()
	{
		$backtrace = debug_backtrace();
		return get_class($backtrace[2]['object']);
	}

	/**
	 * Singleton objects should not be cloned.
	 */
	final private function __clone()
	{
	}
}
