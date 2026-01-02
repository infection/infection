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

namespace Infection\Tests\Ast\Visitor\RemoveUndesiredAttributesVisitor;

use Infection\Ast\Metadata\Annotation;
use Infection\Tests\Ast\AstTestCase;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(RemoveUndesiredAttributesVisitor::class)]
final class RemoveUndesiredAttributesVisitorTest extends AstTestCase
{
    #[DataProvider('attributeProvider')]
    public function test_it_removes_undesired_attributes(
        array $initialAttributes,
        array $desiredAttributes,
        array $expectedAttributes,
    ): void {
        $nodes = $this->parser->parse(
            <<<'PHP'
                <?php

                $x = 10;

                PHP,
        );

        /** @var Node\Stmt\Expression $expression */
        $expression = $nodes[0];

        foreach ($initialAttributes as $key => $value) {
            $expression->setAttribute($key, $value);
        }

        $visitor = new RemoveUndesiredAttributesVisitor(...$desiredAttributes);

        (new NodeTraverser($visitor))->traverse($nodes);

        $actualAttributes = $expression->getAttributes();

        $this->assertSame($expectedAttributes, $actualAttributes);
    }

    public static function attributeProvider(): iterable
    {
        yield 'no attributes' => [
            'initialAttributes' => [],
            'desiredAttributes' => [],
            'expectedAttributes' => [],
        ];

        yield 'keep Annotation enum attributes' => [
            'initialAttributes' => [
                Annotation::ELIGIBLE->name => true,
                Annotation::NOT_COVERED_BY_TESTS->name => true,
                Annotation::ARID_CODE->name => false,
            ],
            'desiredAttributes' => [Annotation::ELIGIBLE, Annotation::ARID_CODE],
            'expectedAttributes' => [
                Annotation::ELIGIBLE->name => true,
                Annotation::ARID_CODE->name => false,
            ],
        ];

        yield 'keep string attributes' => [
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

        yield 'keep mixed Annotation and string attributes' => [
            'initialAttributes' => [
                Annotation::ELIGIBLE->name => true,
                'custom_key' => 'custom_value',
                Annotation::NOT_COVERED_BY_TESTS->name => true,
                'another_key' => 42,
            ],
            'desiredAttributes' => [Annotation::ELIGIBLE, 'another_key'],
            'expectedAttributes' => [
                Annotation::ELIGIBLE->name => true,
                'another_key' => 42,
            ],
        ];

        yield 'remove all attributes when none desired' => [
            'initialAttributes' => [
                Annotation::ELIGIBLE->name => true,
                'custom_key' => 'value',
            ],
            'desiredAttributes' => [],
            'expectedAttributes' => [],
        ];

        yield 'desired attribute not present in initial attributes' => [
            'initialAttributes' => [
                Annotation::ELIGIBLE->name => true,
            ],
            'desiredAttributes' => [Annotation::NOT_COVERED_BY_TESTS, 'missing_key'],
            'expectedAttributes' => [],
        ];
    }
}
