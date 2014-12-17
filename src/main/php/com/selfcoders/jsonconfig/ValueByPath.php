<?php
namespace com\selfcoders\jsonconfig;

class ValueByPath
{
	/**
	 * Get the value of the property in the data tree specified by the dotted path.
	 *
	 * @param \StdClass $dataTree A tree of StdClass objects
	 * @param string $path The dotted path to the property which should be returned
	 * @return null|mixed The value of the property or null if not found
	 */
	public static function getValueByPath($dataTree, $path)
	{
		$pathParts = explode(".", $path);

		foreach ($pathParts as $name)
		{
			if (!isset($dataTree->{$name}))
			{
				return null;
			}

			$dataTree = $dataTree->{$name};
		}

		return $dataTree;
	}

	/**
	 * Set the value of the property specified by the given path in the data tree.
	 *
	 * @param \StdClass $dataTree A tree of StdClass objects
	 * @param string $path The path to the property which should be set
	 * @param mixed $value The value for the property
	 * @param bool $add Whether to add a new property if not existing or only set if existing
	 *
	 * @return bool true if the property has been set successfully, false if not (e.g. property not existing in tree and $add is set to false)
	 */
	public static function setValueByPath($dataTree, $path, $value, $add)
	{
		$pathParts = explode(".", $path);

		$name = $pathParts[0];

		if (!$add and !isset($dataTree->{$name}))
		{
			return false;
		}

		if (count($pathParts) == 1)
		{
			$dataTree->{$name} = $value;

			return true;
		}

		if (isset($dataTree->{$name}))
		{
			$object = $dataTree->{$name};
		}
		else
		{
			$object = new \StdClass;

			$dataTree->{$name} = $object;
		}

		ValueByPath::setValueByPath($object, implode(".", array_slice($pathParts, 1)), $value, $add);

		return true;
	}
}