<?php

namespace Codeception_Basic\Tests\unit;

use Codeception_Basic\SourceClass;

class SourceClassTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testSomeFeature()
    {
        $source = new SourceClass();

        $this->assertSame(3.0, $source->add(1, 2));
    }

    public function test_it_can_add_small_numbers()
    {
        $source = new SourceClass();

        $this->assertSame(0.3, $source->add(0.1, 0.2));
    }
}
