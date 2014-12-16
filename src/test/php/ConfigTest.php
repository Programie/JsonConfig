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
}