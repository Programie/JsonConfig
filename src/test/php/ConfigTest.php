<?php
use com\selfcoders\jsonconfig\Config;

class ConfigTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @var Config
	 */
	private $config;

	public function setUp()
	{
		$this->config = new Config(__DIR__ . "/../resources/config.json", __DIR__ . "/../resources/config.template.json");
	}

	public function testGetDefaultValue()
	{
		$this->assertEquals("Set by template", $this->config->getValue("path.to.my.defaultValue"));
	}

	public function testGetReplacedValue()
	{
		$this->assertEquals("Replaced by user config", $this->config->getValue("path.to.my.value"));
	}

	public function testHasValue()
	{
		$this->assertTrue($this->config->hasValue("path.to.my.value"));
	}

	public function testHasNotValue()
	{
		$this->assertFalse($this->config->hasValue("path.to.not.existing.value"));
	}

	public function testSaveLoad()
	{
		$filename = tempnam(sys_get_temp_dir(), "cfg");

		$saveConfig = new Config(__DIR__ . "/../resources/config.json", __DIR__ . "/../resources/config.template.json");

		$saveConfig->setValue("path.to.my.value", "Some value");

		$saveConfig->save($filename);

		$loadedConfig = new Config($filename, __DIR__ . "/../resources/config.template.json");

		$this->assertEquals("Some value", $loadedConfig->getValue("path.to.my.value"));

		unlink($filename);
	}

	public function testGetUnsetValue()
	{
		$this->setExpectedException("com\\selfcoders\\jsonconfig\\exception\\UnsetConfigValueException");

		$this->config->getValue("path.to.unset.value");
	}

	public function testGetUndefinedValue()
	{
		$this->setExpectedException("com\\selfcoders\\jsonconfig\\exception\\UnknownConfigValueException");

		$this->config->getValue("path.to.not.existing.value");
	}

	public function testSetUndefinedValue()
	{
		$this->setExpectedException("com\\selfcoders\\jsonconfig\\exception\\UnknownConfigValueException");

		$this->config->setValue("path.to.not.existing.value", "some value");
	}

	public function testGetConfigData()
	{
		$data = $this->config->getConfigData();

		$this->assertInstanceOf("StdClass", $data);

		$this->assertInstanceOf("StdClass", $data->{"path.to.unset.value"});

		$this->assertFalse(isset($data->{"path.to.unset.value"}->value));
		$this->assertFalse(isset($data->{"path.to.unset.value"}->defaultValue));

		$this->assertInstanceOf("StdClass", $data->{"path.to.my.value"});

		$this->assertTrue(isset($data->{"path.to.my.value"}->value));
		$this->assertTrue(isset($data->{"path.to.my.value"}->defaultValue));
	}
}