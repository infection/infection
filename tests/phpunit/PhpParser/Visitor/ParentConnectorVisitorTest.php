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

use Infection\PhpParser\Visitor\ParentConnectorVisitor;
use Infection\Tests\Fixtures\PhpParser\ParentConnectorSpyVisitor;
use Infection\Tests\Fixtures\PhpParser\StackSpyVisitor;
use Infection\Tests\SingletonContainer;

/**
 * @group integration
 */
final class ParentConnectorVisitorTest extends BaseVisitorTest
{
    private const CODE = <<<'PHP'
<?php

namespace Foo;

echo 'Hello';
echo ' World!';

namespace Bar;

PHP;

    public function test_it_attaches_the_parent_nodes_to_each_node(): void
    {
        $stackSpyVisitor = new StackSpyVisitor();
        $parentSpyVisitor = new ParentConnectorSpyVisitor();

        $nodes = $this->traverse(
            $this->parseCode(self::CODE),
            [
                $stackSpyVisitor,
                new ParentConnectorVisitor(),
                $parentSpyVisitor,
            ]
        );

//        $this->assertSame(
//            SingletonContainer::getNodeDumper()->dump($stackSpyVisitor->getCollectedNodes()),
//            SingletonContainer::getNodeDumper()->dump($parentSpyVisitor->getCollectedNodes()),
//        );

        $dumper = SingletonContainer::getNodeDumper();

        // Sanity check: just make sure the structure is the one we expect it to be
        // The choice of the structure is important here: we want to clearly display the resulting
        // tree without too much noise - otherwise the tree is much harder to read.
        // In this case what we want to show:
        // - Multiple roots
        // - Multiple children nodes
        // - A case where two children nodes points to the same parent
        $this->assertSame(
            <<<'STR'
array(
    0: Stmt_Namespace(
        name: Name(
            parts: array(
                0: Foo
            )
        )
        stmts: array(
            0: Stmt_Echo(
                exprs: array(
                    0: Scalar_String(
                        value: Hello
                    )
                )
            )
            1: Stmt_Echo(
                exprs: array(
                    0: Scalar_String(
                        value:  World!
                    )
                )
            )
        )
    )
    1: Stmt_Namespace(
        name: Name(
            parts: array(
                0: Bar
            )
        )
        stmts: array(
        )
    )
)
STR
            ,
            $dumper->dump($nodes)
        );

        // Sanity check: display the whole stack flattened out.
        $this->assertSame(
            <<<'STR'
array(
    0: Stmt_Namespace(
        name: Name(
            parts: array(
                0: Foo
            )
        )
        stmts: array(
            0: Stmt_Echo(
                exprs: array(
                    0: Scalar_String(
                        value: Hello
                    )
                )
            )
            1: Stmt_Echo(
                exprs: array(
                    0: Scalar_String(
                        value:  World!
                    )
                )
            )
        )
    )
    1: Name(
        parts: array(
            0: Foo
        )
    )
    2: Stmt_Echo(
        exprs: array(
            0: Scalar_String(
                value: Hello
            )
        )
    )
    3: Scalar_String(
        value: Hello
    )
    4: Stmt_Echo(
        exprs: array(
            0: Scalar_String(
                value:  World!
            )
        )
    )
    5: Scalar_String(
        value:  World!
    )
    6: Stmt_Namespace(
        name: Name(
            parts: array(
                0: Bar
            )
        )
        stmts: array(
        )
    )
    7: Name(
        parts: array(
            0: Bar
        )
    )
)
STR
            ,
            $dumper->dump($stackSpyVisitor->getCollectedNodes())
        );

        $this->assertSame(
            <<<'STR'
array(
    0: null
    1: Stmt_Namespace(
        name: Name(
            parts: array(
                0: Foo
            )
        )
        stmts: array(
            0: Stmt_Echo(
                exprs: array(
                    0: Scalar_String(
                        value: Hello
                    )
                )
            )
            1: Stmt_Echo(
                exprs: array(
                    0: Scalar_String(
                        value:  World!
                    )
                )
            )
        )
    )
    2: Stmt_Namespace(
        name: Name(
            parts: array(
                0: Foo
            )
        )
        stmts: array(
            0: Stmt_Echo(
                exprs: array(
                    0: Scalar_String(
                        value: Hello
                    )
                )
            )
            1: Stmt_Echo(
                exprs: array(
                    0: Scalar_String(
                        value:  World!
                    )
                )
            )
        )
    )
    3: Stmt_Echo(
        exprs: array(
            0: Scalar_String(
                value: Hello
            )
        )
    )
    4: Stmt_Namespace(
        name: Name(
            parts: array(
                0: Foo
            )
        )
        stmts: array(
            0: Stmt_Echo(
                exprs: array(
                    0: Scalar_String(
                        value: Hello
                    )
                )
            )
            1: Stmt_Echo(
                exprs: array(
                    0: Scalar_String(
                        value:  World!
                    )
                )
            )
        )
    )
    5: Stmt_Echo(
        exprs: array(
            0: Scalar_String(
                value:  World!
            )
        )
    )
    6: null
    7: Stmt_Namespace(
        name: Name(
            parts: array(
                0: Bar
            )
        )
        stmts: array(
        )
    )
)
STR
            ,
            $dumper->dump($parentSpyVisitor->getCollectedNodes())
        );
    }
}
