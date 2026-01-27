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

use Infection\Framework\Str;
use Infection\Mutation\Mutation;
use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\PhpParser\MutatedNode;
use Infection\PhpParser\Visitor\MutatorVisitor;
use Infection\Testing\MutatorName;
use Infection\Testing\SingletonContainer;
use LogicException;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[CoversClass(MutatorVisitor::class)]
final class MutatorVisitorTest extends BaseVisitorTestCase
{
    /**
     * @param Node[] $nodes
     */
    #[DataProvider('providesMutationCases')]
    public function test_it_mutates_the_correct_node(
        array $nodes,
        string $expectedCodeOutput,
        Mutation $mutation,
    ): void {
        $updatedNodes = $this->traverse(
            $nodes,
            [
                new CloningVisitor(),
                new MutatorVisitor($mutation),
            ],
        );

        $output = SingletonContainer::getPrinter()->print($updatedNodes, $mutation);

        $this->assertSame($expectedCodeOutput, Str::rTrimLines($output));
    }

    public static function providesMutationCases(): iterable
    {
        yield 'it mutates the correct node' => (static function (): iterable {
            $code = <<<'PHP'
                <?php

                class Test
                {
                    public function hello(): string
                    {
                        return 'hello';
                    }
                    public function bye(): string
                    {
                        return 'bye';
                    }
                }
                PHP;

            [$nodes, $tokens] = self::parseCode($code);

            return [
                $nodes,
                <<<'PHP'
                    <?php

                    class Test
                    {
                        public function hello(): string
                        {
                            return 'hello';
                        }

                    }
                    PHP,
                new Mutation(
                    'path/to/file',
                    $nodes,
                    PublicVisibility::class,
                    MutatorName::getName(PublicVisibility::class),
                    [
                        'startTokenPos' => 28,
                        'endTokenPos' => 46,
                        'startLine' => -1,
                        'endLine' => -1,
                        'startFilePos' => -1,
                        'endFilePos' => -1,
                    ],
                    ClassMethod::class,
                    MutatedNode::wrap(new Nop()),
                    0,
                    [],
                    $tokens,
                    $code,
                ),
            ];
        })();

        yield 'it can mutate the node with multiple-ones' => (static function (): iterable {
            $code = <<<'PHP'
                <?php

                class Test
                {
                    public function hello(): string
                    {
                        return 'hello';
                    }
                    public function bye(): string
                    {
                        return 'bye';
                    }
                }
                PHP;

            [$nodes, $tokens] = self::parseCode($code);

            return [
                $nodes,
                <<<'PHP'
                    <?php

                    class Test
                    {
                        public function hello(): string
                        {
                            return 'hello';
                        }


                    }
                    PHP,
                new Mutation(
                    'path/to/file',
                    $nodes,
                    PublicVisibility::class,
                    MutatorName::getName(PublicVisibility::class),
                    [
                        'startTokenPos' => 28,
                        'endTokenPos' => 46,
                        'startLine' => -1,
                        'endLine' => -1,
                        'startFilePos' => -1,
                        'endFilePos' => -1,
                    ],
                    ClassMethod::class,
                    MutatedNode::wrap([new Nop(), new Nop()]),
                    0,
                    [],
                    $tokens,
                    $code,
                ),
            ];
        })();

        yield 'it does not mutate if only one of start or end position is correctly set' => (static function (): iterable {
            $code = <<<'PHP'
                <?php

                class Test
                {
                    public function hello(): string
                    {
                        return 'hello';
                    }
                    public function bye(): string
                    {
                        return 'bye';
                    }
                }
                PHP;

            [$nodes, $tokens] = self::parseCode($code);

            return [
                $nodes,
                <<<'PHP'
                    <?php

                    class Test
                    {
                        public function hello(): string
                        {
                            return 'hello';
                        }
                        public function bye(): string
                        {
                            return 'bye';
                        }
                    }
                    PHP,
                new Mutation(
                    'path/to/file',
                    $nodes,
                    PublicVisibility::class,
                    MutatorName::getName(PublicVisibility::class),
                    [
                        'startTokenPos' => 29,
                        'endTokenPos' => 50,
                        'startLine' => -1,
                        'endLine' => -1,
                        'startFilePos' => -1,
                        'endFilePos' => -1,
                    ],
                    ClassMethod::class,
                    MutatedNode::wrap(new Nop()),
                    0,
                    [],
                    $tokens,
                    $code,
                ),
            ];
        })();

        yield 'it does not mutate if the parser does not contain startTokenPos' => (static function (): iterable {
            $badParser = (new ParserFactory())->createForNewestSupportedVersion();

            return [
                $nodes = $badParser->parse(<<<'PHP'
                    <?php

                    class Test
                    {
                        public function hello(): string
                        {
                            return 'hello';
                        }
                        public function bye(): string
                        {
                            return 'bye';
                        }
                    }
                    PHP
                ) ?? throw new LogicException(),
                <<<'PHP'
                    <?php

                    class Test
                    {
                        public function hello(): string
                        {
                            return 'hello';
                        }
                        public function bye(): string
                        {
                            return 'bye';
                        }
                    }
                    PHP,
                new Mutation(
                    'path/to/file',
                    $nodes,
                    PublicVisibility::class,
                    MutatorName::getName(PublicVisibility::class),
                    [
                        'startTokenPos' => 29,
                        'endTokenPos' => 48,
                        'startLine' => -1,
                        'endLine' => -1,
                        'startFilePos' => -1,
                        'endFilePos' => -1,
                    ],
                    MutatorName::getName(PublicVisibility::class),
                    MutatedNode::wrap(new Nop()),
                    0,
                    [],
                    $badParser->getTokens(),
                    '',
                ),
            ];
        })();
    }
}
