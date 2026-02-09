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

namespace Infection\Tests\TestingUtility\PhpParser\Visitor\KeepOnlyDesiredAttributesVisitor;

use Infection\Tests\PhpParser\Visitor\VisitorTestCase;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(KeepOnlyDesiredAttributesVisitor::class)]
final class KeepOnlyDesiredAttributesVisitorTest extends VisitorTestCase
{
    /**
     * @param array<string, mixed> $initialAttributes
     * @param list<string> $desiredAttributes
     * @param array<string, mixed> $expectedAttributes
     */
    #[DataProvider('attributeProvider')]
    public function test_it_keeps_only_the_desired_attributes(
        array $initialAttributes,
        array $desiredAttributes,
        array $expectedAttributes,
    ): void {
        $node = new Node\Stmt\Expression(
            new Node\Expr\Assign(
                new Node\Expr\Variable('x'),
                new Node\Scalar\Int_(42),
            ),
            $initialAttributes,
        );

        $visitor = new KeepOnlyDesiredAttributesVisitor(...$desiredAttributes);

        (new NodeTraverser($visitor))->traverse([$node]);

        $actualAttributes = $node->getAttributes();

        $this->assertSame($expectedAttributes, $actualAttributes);
    }

    public static function attributeProvider(): iterable
    {
        yield 'no attributes' => [
            'initialAttributes' => [],
            'desiredAttributes' => [],
            'expectedAttributes' => [],
        ];

        yield 'keep some attributes' => [
            'initialAttributes' => [
                'custom_key_1' => 'value1',
                'custom_key_2' => 'value2',
                'custom_key_3' => 'value3',
            ],
            'desiredAttributes' => ['custom_key_1', 'custom_key_3'],
            'expectedAttributes' => [
                'custom_key_1' => 'value1',
                'custom_key_3' => 'value3',
            ],
        ];
    }
}
