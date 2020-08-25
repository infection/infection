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

namespace Infection\Tests\PhpParser\Visitor;

use Infection\PhpParser\Visitor\FullyQualifiedClassNameManipulator;
use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Nop;
use PHPUnit\Framework\TestCase;

final class FullyQualifiedClassNameManipulatorTest extends TestCase
{
    /**
     * @dataProvider hasFqcnProvider
     */
    public function test_it_can_determine_if_the_given_node_has_a_fqcn_attribute(
        Node $node,
        bool $expected
    ): void {
        $this->assertSame($expected, FullyQualifiedClassNameManipulator::hasFqcn($node));
    }

    public function test_it_can_provide_the_node_fqcn(): void
    {
        $fqcn = new FullyQualified('Acme\Foo');
        $node = new Nop(['fullyQualifiedClassName' => $fqcn]);

        $this->assertSame($fqcn, FullyQualifiedClassNameManipulator::getFqcn($node));
    }

    public function test_it_can_provide_the_node_fqcn_for_an_anonymous_class(): void
    {
        $node = new Nop(['fullyQualifiedClassName' => null]);

        $this->assertNull(FullyQualifiedClassNameManipulator::getFqcn($node));
    }

    public function test_it_cannot_provide_the_node_fqcn_if_has_not_be_set_yet(): void
    {
        $node = new Nop();

        $this->expectException(InvalidArgumentException::class);

        // We are not interested in a more helpful message here since it would be the result of
        // a misconfiguration on our part rather than a user one. Plus this would require some
        // extra processing on a part which is quite a hot path.

        FullyQualifiedClassNameManipulator::getFqcn($node);
    }

    public function test_it_can_set_a_node_fqcn(): void
    {
        $fqcn = new FullyQualified('Acme\Foo');
        $node = new Nop();

        FullyQualifiedClassNameManipulator::setFqcn($node, $fqcn);

        $this->assertSame($fqcn, FullyQualifiedClassNameManipulator::getFqcn($node));
    }

    public function test_it_can_set_a_node_fqcn_for_an_anonymous_class(): void
    {
        $node = new Nop();

        FullyQualifiedClassNameManipulator::setFqcn($node, null);

        $this->assertNull(FullyQualifiedClassNameManipulator::getFqcn($node));
    }

    public static function hasFqcnProvider(): iterable
    {
        yield 'no FQCN' => [
            new Nop(),
            false,
        ];

        yield 'empty string FQCN' => [
            new Nop(['fullyQualifiedClassName' => '']),
            true,
        ];

        yield 'null FQCN' => [
            new Nop(['fullyQualifiedClassName' => null]),
            true,
        ];

        yield 'has FQCN' => [
            new Nop(['fullyQualifiedClassName' => 'Acme\Foo']),
            true,
        ];
    }
}
