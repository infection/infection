<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\Util;

use Infection\Mutator\Util\AbstractValueToNullReturnValue;
use PhpParser\Node;
use PhpParser\Node\Stmt\Function_;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class AbstractValueToNullReturnValueTest extends TestCase
{
    protected $testSubject;

    protected function setUp(): void
    {
        $this->testSubject = $this->getMockBuilder(AbstractValueToNullReturnValue::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    public function test_attribute_not_found(): void
    {
        $this->assertTrue($this->invokeMethod($this->mockNode(null)));
    }

    public function test_return_type_is_node_identifier(): void
    {
        /** @var Node\Identifier $mockNode */
        $mockNode = $this->createMock(Node\Identifier::class);

        $this->assertTrue(
            $this->invokeMethod(
                $this->mockNode(
                    $this->mockFunction($mockNode)
                )
            )
        );
    }

    public function test_return_type_is_scalar_typehint(): void
    {
        $this->assertFalse(
            $this->invokeMethod(
                $this->mockNode(
                    $this->mockFunction('int')
                )
            )
        );
    }

    public function test_return_type_is_nullable(): void
    {
        $this->assertTrue(
            $this->invokeMethod(
                $this->mockNode(
                    $this->mockFunction(
                        $this->createMock(Node\NullableType::class)
                    )
                )
            )
        );
    }

    public function test_return_type_is_node_name(): void
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

    public function test_return_type_is_not_node_name(): void
    {
        $this->assertFalse(
            $this->invokeMethod(
                $this->mockNode(
                    $this->mockFunction(
                        $this->createMock(Node\Name::class)
                    )
                )
            )
        );
    }

    private function mockNode($returnValue): Node
    {
        /** @var Node|MockObject $mockNode */
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
        /** @var Function_|MockObject $mockFunction */
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
}
