<?php

namespace App\Tests\unit\Inner;

use Codeception_Basic\Inner\InnerSourceClass;

class InnerSourceClassTest extends \Codeception\Test\Unit
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
    public function testSubtraction()
    {
        $source = new InnerSourceClass();

        $this->assertNotSame(-1.23, $source->sub(1, 2));
    }
}
