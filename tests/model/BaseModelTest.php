<?php

abstract class BaseModelTest extends PHPUnit_Framework_TestCase {
    protected $class = null;

    public function testCreate() {
        $class = $this->class;

        if ($class === null) {
            $this->fail("Child classes must override the \$class variable.");
        }

        $this->assertTrue($class::create([]) instanceof $this->class);
    }
}