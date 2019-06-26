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

namespace Infection\Tests\Visitor;

use Infection\Visitor\NotMutableIgnoreVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class NotMutableIgnoreVisitorTest extends AbstractBaseVisitorTest
{
    private $spyVisitor;

    protected function setUp(): void
    {
        $this->spyVisitor = $this->getSpyVisitor();
    }

    public function test_it_does_not_traverse_interface_methods(): void
    {
        $code = <<<'PHP'
<?php

interface Foo
{
    public function foo(): array;
    public function bar(int $number): string;
}
PHP;
        $this->parseAndTraverse($code);
        $this->assertSame(0, $this->spyVisitor->getNumberOfClassMethodsVisited());
    }

    public function test_it_does_not_traverse_abstract_methods(): void
    {
        $code = <<<'PHP'
<?php

abstract class Foo
{
    abstract public function foo(): array;
    abstract public function bar(int $number): string;
}
PHP;
        $this->parseAndTraverse($code);
        $this->assertSame(0, $this->spyVisitor->getNumberOfClassMethodsVisited());
    }

    public function test_it_still_traverses_normal_methods_in_abstract_classes(): void
    {
        $code = <<<'PHP'
<?php

abstract class Foo
{
    abstract public function foo(): array;
    public function bar(int $number): string { return ''; }
}
PHP;
        $this->parseAndTraverse($code);
        $this->assertSame(1, $this->spyVisitor->getNumberOfClassMethodsVisited());
    }

    private function getSpyVisitor()
    {
        return new class() extends NodeVisitorAbstract {
            private $nodesVisitedCount = 0;

            public function leaveNode(Node $node): void
            {
                if ($node instanceof Node\Stmt\ClassMethod) {
                    ++$this->nodesVisitedCount;
                }
            }

            public function getNumberOfClassMethodsVisited(): int
            {
                return $this->nodesVisitedCount;
            }
        };
    }

    private function parseAndTraverse(string $code): void
    {
        $nodes = $this->getNodes($code);

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new NotMutableIgnoreVisitor());
        $traverser->addVisitor($this->spyVisitor);

        $traverser->traverse($nodes);
    }
}
