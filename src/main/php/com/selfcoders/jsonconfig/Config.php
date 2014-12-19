<?php
namespace com\selfcoders\jsonconfig;

use com\selfcoders\jsonconfig\exception\UnknownConfigValueException;
use com\selfcoders\jsonconfig\exception\UnsetConfigValueException;

class Config
{
	/**
	 * @var string The path to the file containing user configuration as JSON
	 */
	private $configFile;
	/**
	 * @var string The path to the file containing the template as JSON
	 */
	private $templateFile;
	/**
	 * @var \StdClass A map of configuration values
	 * <pre>
	 *  The key of each property is the dotted path to the value.
	 *  Each property is a map containing the following properties:
	 *  - value: The overwritten value read from the user configuration file
	 *  - default: The default value read from the template file
	 * </pre>
	 */
	private $configData;

	public function __construct($configFile, $templateFile)
	{
		$this->configFile = $configFile;
		$this->templateFile = $templateFile;

		$this->load();
	}

	/**
	 * Load or reload the configuration from the specified configuration file and template
	 *
	 * @param null|string $configFile An optional path to a JSON file which should be loaded instead of the $configFile defined on instance creation
	 */
	public function load($configFile = null)
	{
		$this->configData = json_decode(file_get_contents($this->templateFile));

		if ($configFile == null)
		{
			$configFile = $this->configFile;
		}

		if (file_exists($configFile))
		{
			$configData = json_decode(file_get_contents($configFile));
			if ($configData)
			{
				foreach ($this->configData as $name => $itemData)
				{
					$itemData->value = ValueByPath::getValueByPath($configData, $name);
				}
			}
		}
	}

	/**
	 * Save the configuration to the specified configuration file
	 *
	 * @param null|string $configFile An optional path to a JSON file to which the data should be written instead of the $configFile defined on instance creation
	 * @param int $jsonOptions Optional options passed to json_encode (e.g. JSON_PRETTY_PRINT)
	 */
	public function save($configFile = null, $jsonOptions = 0)
	{
		if ($configFile == null)
		{
			$configFile = $this->configFile;
		}

		$data = new \StdClass;

		foreach ($this->configData as $name => $itemData)
		{
			if (isset($itemData->value))
			{
				ValueByPath::setValueByPath($data, $name, $itemData->value, true);
			}

			if (isset($itemData->value) and isset($itemData->defaultValue) and $itemData->value == $itemData->defaultValue)
			{
				ValueByPath::removeValueByPath($data, $name);
			}
		}

		file_put_contents($configFile, json_encode($data, $jsonOptions));
	}

	/**
	 * Check whether the given configuration value is valid.
	 *
	 * Note: Only values specified in the template are valid!
	 *
	 * @param string $name The dotted path to the configuration value (e.g. "path.to.the.value")
	 *
	 * @return bool true if the value exists, false otherwise
	 */
	public function hasValue($name)
	{
		return isset($this->configData->{$name});
	}

	/**
	 * Get the value of the given configuration path.
	 *
	 * @param string $name The dotted path to the configuration value (e.g. "path.to.the.value")
	 *
	 * @return mixed The value of the configuration value. This can be anything JSON supports (e.g. boolean, integer, string, array or map).
	 *
	 * @throws UnknownConfigValueException If the configuration value is not defined in the template
	 * @throws UnsetConfigValueException If the configuration value is not set in the configuration file and no default value has been specified
	 */
	public function getValue($name)
	{
		if (!$this->hasValue($name))
		{
			throw new UnknownConfigValueException($name);
		}

		$itemData = $this->configData->{$name};

		if (isset($itemData->value))
		{
			return $itemData->value;
		}

		if (isset($itemData->defaultValue))
		{
			return $itemData->defaultValue;
		}

		throw new UnsetConfigValueException($name);
	}

	/**
	 * Set the value of the given property path.
	 *
	 * @param string $name The dotted path to the configuration value (e.g. "path.to.the.value")
	 * @param mixed $value The value for the configuration value. This can be anything JSON supports (e.g. boolean, integer, string, array or map)
	 *
	 * @throws UnknownConfigValueException If the configuration value is not defined in the template
	 */
	public function setValue($name, $value)
	{
		if (!$this->hasValue($name))
		{
			throw new UnknownConfigValueException($name);
		}

		$itemData = $this->configData->{$name};

		if ($value === $itemData->defaultValue)
		{
			unset($itemData->value);
		}
		else
		{
			$itemData->value = $value;
		}
	}

	/**
	 * Get the full configuration data map
	 *
	 * @return \StdClass The configuration data map
	 *
	 * @see $configData
	 */
	public function getConfigData()
	{
		return $this->configData;
	}
}