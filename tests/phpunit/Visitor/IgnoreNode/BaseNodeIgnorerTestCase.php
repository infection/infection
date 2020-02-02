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

namespace Infection\Tests\Visitor\IgnoreNode;

use Infection\Container;
use Infection\Visitor\IgnoreNode\NodeIgnorer;
use Infection\Visitor\NonMutableNodesIgnorerVisitor;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PHPUnit\Framework\TestCase;

abstract class BaseNodeIgnorerTestCase extends TestCase
{
    /**
     * @var Parser|null
     */
    private static $parser;

    abstract protected function getIgnore(): NodeIgnorer;

    final protected function parseAndTraverse(string $code, NodeVisitor $spy): void
    {
        $nodes = $this->getParser()->parse($code);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NonMutableNodesIgnorerVisitor([$this->getIgnore()]));
        $traverser->addVisitor($spy);

        $traverser->traverse($nodes);
        $this->addToAssertionCount(1);
    }

    protected function createSpy(): IgnoreSpyVisitor
    {
        return new IgnoreSpyVisitor(static function (): void {
            self::fail('A variable that should have been ignored was still parsed by the next visitor.');
        });
    }

    private function getParser(): Parser
    {
        if (self::$parser === null) {
            self::$parser = Container::create()->getParser();
        }

        return self::$parser;
    }
}
