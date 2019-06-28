<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class PartialMockTestClass
{
    public $constructorCalled = false;

    public function __construct()
    {
        $this->constructorCalled = true;
    }

    public function doSomething()
    {
    }

    public function doAnotherThing()
    {
    }
}
