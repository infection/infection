<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

namespace Infection\Tests\Mutator\Util;

use Infection\Mutator\Util\AbstractValueToNullReturnValue;
use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PHPUnit\Framework\TestCase;

class AbstractValueToNullReturnValueTest extends TestCase
{
    protected $testSubject = null;

    public function setUp()
    {
        $this->testSubject = $this->getMockBuilder(AbstractValueToNullReturnValue::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    private function mockNode($returnValue): Node
    {
        $mockNode = $this->getMockBuilder(Node::class)
                         ->disableOriginalConstructor()
                         ->setMethods(['getAttribute'])
                         ->getMockForAbstractClass();

        $mockNode->method('getAttribute')
                 ->willReturn($returnValue);

        return $mockNode;
    }

    private function mockFunction($returnValue): Function_
    {
        $mockFunction = $this->getMockBuilder(Function_::class)
            ->disableOriginalConstructor()
            ->setMethods(['getReturnType'])
            ->getMock();

        $mockFunction->method('getReturnType')
            ->willReturn($returnValue);

        return $mockFunction;
    }

    private function invokeMethod(Node $mockNode)
    {
        $reflectionMethod = new \ReflectionMethod(AbstractValueToNullReturnValue::class, 'isNullReturnValueAllowed');
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invoke($this->testSubject, $mockNode);
    }

    public function test_attribute_not_found()
    {
        $this->assertTrue($this->invokeMethod($this->mockNode(null)));
    }

    public function test_return_type_is_node_identifier()
    {
        /** @var Node\Identifier $mockNode */
        $mockNode = $this->getMockBuilder(Node\Identifier::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockNode->name = null;

        $this->assertTrue(
            $this->invokeMethod(
                $this->mockNode(
                    $this->mockFunction($mockNode)
                )
            )
        );
    }

    public function test_return_type_is_scalar_typehint()
    {
        $this->assertFalse(
            $this->invokeMethod(
                $this->mockNode(
                    $this->mockFunction('int')
                )
            )
        );
    }

    public function test_return_type_is_nullable()
    {
        $mockNode = $this->getMockBuilder(Node\NullableType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertTrue(
            $this->invokeMethod(
                $this->mockNode(
                    $this->mockFunction(
                        $mockNode
                    )
                )
            )
        );
    }

    public function test_return_type_is_node_name()
    {
        $this->assertTrue(
            $this->invokeMethod(
                $this->mockNode(
                    $this->mockFunction(
                        new \stdClass()
                    )
                )
            )
        );
    }

    public function test_return_type_is_not_node_name()
    {
        $mockNode = $this->getMockBuilder(Node\Name::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertFalse(
            $this->invokeMethod(
                $this->mockNode(
                    $this->mockFunction(
                        $mockNode
                    )
                )
            )
        );
    }
}
