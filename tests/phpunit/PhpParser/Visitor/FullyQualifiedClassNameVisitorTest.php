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

use function array_map;
use Infection\PhpParser\Visitor\FullyQualifiedClassNameManipulator;
use Infection\PhpParser\Visitor\FullyQualifiedClassNameVisitor;
use Infection\Tests\Fixtures\PhpParser\FullyQualifiedClassNameSpyVisitor;
use PhpParser\Node;

/**
 * @group integration
 */
final class FullyQualifiedClassNameVisitorTest extends BaseVisitorTest
{
    /**
     * @dataProvider codeProvider
     *
     * @param array<array{string, string}> $expected
     */
    public function test_it_adds_FQCN_to_the_appropriate_node(string $code, array $expected): void
    {
        $spyVisitor = new FullyQualifiedClassNameSpyVisitor();

        $this->traverse(
            $this->parseCode($code),
            [
                new FullyQualifiedClassNameVisitor(),
                $spyVisitor,
            ]
        );

        $actualProcessedNodesFqcn = array_map(
            static function (Node $node): array {
                return [
                    $node->getType(),
                    (string) FullyQualifiedClassNameManipulator::getFqcn($node),
                ];
            },
            $spyVisitor->getCollectedNodes()
        );

        $this->assertSame($expected, $actualProcessedNodesFqcn);
    }

    public function codeProvider(): iterable
    {
        yield 'global class' => [
            <<<'PHP'
<?php

class Foo {}
PHP
            ,
            [
                ['Stmt_Class', 'Foo'],
            ],
        ];

        yield 'global abstract class' => [
            <<<'PHP'
<?php

abstract class Foo {}
PHP
            ,
            [
                ['Stmt_Class', 'Foo'],
            ],
        ];

        yield 'global final class' => [
            <<<'PHP'
<?php

abstract class Foo {}
PHP
            ,
            [
                ['Stmt_Class', 'Foo'],
            ],
        ];

        yield 'namespaced class' => [
            <<<'PHP'
<?php

namespace Acme;

class Foo {}
PHP
            ,
            [
                ['Stmt_Class', 'Acme\Foo'],
            ],
        ];

        yield 'global interface' => [
            <<<'PHP'
<?php

interface Foo {}
PHP
            ,
            [
                ['Stmt_Interface', 'Foo'],
            ],
        ];

        yield 'namespaced interface' => [
            <<<'PHP'
<?php

namespace Acme;

interface Foo {}
PHP
            ,
            [
                ['Stmt_Interface', 'Acme\Foo'],
            ],
        ];

        yield 'global anonymous class' => [
            <<<'PHP'
<?php

new class() extends SplFileInfo {};
PHP
            ,
            [
                ['Stmt_Class', ''],
            ],
        ];

        yield 'namespaced anonymous class' => [
            <<<'PHP'
<?php

namespace Acme;

new class() extends SplFileInfo {};
PHP
            ,
            [
                ['Stmt_Class', ''],
            ],
        ];

        // We ignore regular instances but not in the case of anonymous classes. The reason being
        // we are only interested in mutating a class-like _body_. So an instance is not
        // interesting. However, in the case of an anonymous class, a new instance is _also_ the
        // class body declaration.
        yield 'ignore regular instances' => [
            <<<'PHP'
<?php

new Foo();
PHP
            ,
            [],
        ];

        yield 'multiple namespace classes with multiple classes' => [
            <<<'PHP'
<?php

namespace X {
    class Foo {}
}

namespace Y {
    class Bar {}
    class Baz {}
}

namespace {
    class Faz {}
}
PHP
            ,
            [
                ['Stmt_Class', 'X\Foo'],
                ['Stmt_Class', 'Y\Bar'],
                ['Stmt_Class', 'Y\Baz'],
                ['Stmt_Class', 'Faz'],
            ],
        ];
    }
}
