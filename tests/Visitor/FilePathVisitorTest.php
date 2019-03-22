<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
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

use Infection\Visitor\FilePathVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class FilePathVisitorTest extends AbstractBaseVisitorTest
{
    public function test_it_sets_all_nodes_file_path(): void
    {
        $path = 'foo/bar/bar.php';
        $nodes = $this->getNodes(<<<PHP
<?php

class Foo
{
    public function bar(): int
    {
        return 721;
    }
}
PHP
);

        $traverser = new NodeTraverser();
        $spy = $this->getSpyVisitor($path);
        $traverser->addVisitor(new FilePathVisitor($path));
        $traverser->addVisitor($spy);
        $traverser->traverse($nodes);
        $this->assertFalse($spy->foundWrongPath);
    }

    private function getSpyVisitor(string $path)
    {
        return new class($path) extends NodeVisitorAbstract {
            private $path;

            public $foundWrongPath = false;

            public function __construct($path)
            {
                $this->path = $path;
            }

            public function leaveNode(Node $node): void
            {
                if ($node->getAttribute(FilePathVisitor::FILE_PATH) !== $this->path) {
                    $this->foundWrongPath = true;
                }
            }
        };
    }
}
