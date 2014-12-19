<?php
use com\selfcoders\jsonconfig\ValueByPath;

class ValueByPathTest extends PHPUnit_Framework_TestCase
{
	public function testGetValueByPath()
	{
		$c = new StdClass;
		$c->d = "This is the value";

		$b = new StdClass;
		$b->c = $c;

		$a = new StdClass;
		$a->b = $b;

		$tree = new StdClass;
		$tree->a = $a;

		$this->assertEquals("This is the value", ValueByPath::getValueByPath($tree, "a.b.c.d"));
	}

	public function testSetValueByPath()
	{
		$tree = new StdClass;

		ValueByPath::setValueByPath($tree, "a.b.c.d", "New value", true);

		$this->assertTrue(isset($tree->a->b->c->d));

		$this->assertEquals("New value", $tree->a->b->c->d);
	}

	public function testSetValueByPathExisting()
	{
		$c = new StdClass;
		$c->d = "This is the old value";

		$b = new StdClass;
		$b->c = $c;

		$a = new StdClass;
		$a->b = $b;

		$tree = new StdClass;
		$tree->a = $a;

		ValueByPath::setValueByPath($tree, "a.b.c.d", "This is the new value", true);

		$this->assertEquals("This is the new value", $tree->a->b->c->d);
	}

	public function testSetValueByPathNotExisting()
	{
		$tree = new StdClass;

		$this->assertFalse(ValueByPath::setValueByPath($tree, "a.b.c.d", "New value", false));

		$this->assertFalse(isset($tree->a->b->c->d));
	}

	public function testRemoveValueByPath()
	{
		$c = new StdClass;
		$c->d1 = "This is the value";
		$c->d2 = "This will be removed";

		$b = new StdClass;
		$b->c = $c;

		$a = new StdClass;
		$a->b = $b;

		$tree = new StdClass;
		$tree->a = $a;

		$this->assertTrue(ValueByPath::removeValueByPath($tree, "a.b.c.d2"));

		$this->assertTrue(isset($tree->a->b->c->d1));
		$this->assertFalse(isset($tree->a->b->c->d2));
	}

	public function testRemoveValueByPathNotExisting()
	{
		$c = new StdClass;
		$c->d1 = "This is the value";
		$c->d2 = "This will be removed";

		$b = new StdClass;
		$b->c = $c;

		$a = new StdClass;
		$a->b = $b;

		$tree = new StdClass;
		$tree->a = $a;

		$this->assertFalse(ValueByPath::removeValueByPath($tree, "a.b.c.d3"));

		$this->assertTrue(isset($tree->a->b->c->d1));
		$this->assertTrue(isset($tree->a->b->c->d2));
	}
}