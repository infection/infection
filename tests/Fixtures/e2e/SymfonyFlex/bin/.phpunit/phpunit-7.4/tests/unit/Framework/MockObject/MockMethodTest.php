<?php
declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\TestCase;

class MockMethodTest extends TestCase
{
    public function testGetNameReturnsMethodName()
    {
        $method = new MockMethod(
            'ClassName',
            'methodName',
            false,
            '',
            '',
            '',
            '',
            '',
            false,
            false,
            null,
            false
        );
        $this->assertEquals('methodName', $method->getName());
    }

    public function testFailWhenReturnTypeIsParentButThereIsNoParentClass()
    {
        $method = new MockMethod(
            \stdClass::class,
            'methodName',
            false,
            '',
            '',
            '',
            'parent',
            '',
            false,
            false,
            null,
            false
        );
        $this->expectException(\RuntimeException::class);
        $method->generateCode();
    }
}
