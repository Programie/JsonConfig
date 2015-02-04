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

	public function testSaveDefaultValue()
	{
		$filename = tempnam(sys_get_temp_dir(), "cfg");

		$saveConfig = new Config(__DIR__ . "/../resources/config.json", __DIR__ . "/../resources/config.template.json");

		$saveConfig->setValue("path.to.another.path", "This is the default value");

		$saveConfig->save($filename);

		$json = json_decode(file_get_contents($filename));

		$this->assertFalse(isset($json->path->to->another->path));

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

	public function testHasDefaultValue()
	{
		$this->assertTrue($this->config->hasDefaultValue("path.to.my.defaultValue"));
		$this->assertFalse($this->config->hasDefaultValue("path.to.my.otherValue"));
	}

	public function testIsValueSet()
	{
		$this->assertTrue($this->config->isValueSet("path.to.my.value"));
		$this->assertTrue($this->config->isValueSet("path.to.my.otherValue"));
		$this->assertFalse($this->config->isValueSet("path.to.unset.value"));
	}

	public function testSaveUnset()
	{
		$filename = tempnam(sys_get_temp_dir(), "cfg");

		$this->config->save($filename, 0, true);
		$json = json_decode(file_get_contents($filename));

		$this->assertEquals("Set by template", $json->path->to->my->defaultValue);
		$this->assertNull($json->path->to->unset->value);

		unlink($filename);
	}

	public function testLoadInvalidJson()
	{
		$this->setExpectedException("com\\selfcoders\\jsonconfig\\exception\\JsonException");

		new Config(__DIR__ . "/../resources/invalid.json", __DIR__ . "/../resources/config.template.json");
	}
}