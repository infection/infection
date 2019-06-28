<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
abstract class AbstractMockTestClass implements MockTestInterface
{
    abstract public function doSomething();

    public function returnAnything()
    {
        return 1;
    }
}
