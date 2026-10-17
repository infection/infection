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

namespace Infection\PhpParser\Visitor;

use function array_key_last;
use function array_pop;
use Override;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use function sprintf;

/**
 * Tracks the lexical class and method used by selection.
 *
 * @internal
 */
final class CollectSourceSymbolsVisitor extends NodeVisitorAbstract
{
    /**
     * @var list<Node\Stmt\ClassLike>
     */
    private array $classStack = [];

    /**
     * @var list<Node\Stmt\ClassMethod>
     */
    private array $methodStack = [];

    /**
     * @var list<array{Node\Stmt\ClassLike|null, Node\Stmt\ClassMethod|null}>
     */
    private array $declarations = [];

    /**
     * @var list<string>|null
     */
    private ?array $symbols = null;

    #[Override]
    public function beforeTraverse(array $nodes): null
    {
        $this->classStack = [];
        $this->methodStack = [];
        $this->declarations = [];
        $this->symbols = null;

        return null;
    }

    #[Override]
    public function enterNode(Node $node): null
    {
        if ($node instanceof Node\Stmt\ClassLike) {
            $this->classStack[] = $node;
            $this->declarations[] = [
                $node,
                null,
            ];
            $this->symbols = null;
        }

        if ($node instanceof Node\Stmt\ClassMethod) {
            $this->methodStack[] = $node;
            $this->declarations[] = [
                $this->findClass(),
                $node,
            ];
            $this->symbols = null;
        }

        return null;
    }

    #[Override]
    public function leaveNode(Node $node): null
    {
        if ($node instanceof Node\Stmt\ClassMethod) {
            array_pop($this->methodStack);
        }

        if ($node instanceof Node\Stmt\ClassLike) {
            array_pop($this->classStack);
        }

        return null;
    }

    public function findClass(): ?Node\Stmt\ClassLike
    {
        return self::findCurrent($this->classStack);
    }

    public function findMethod(): ?Node\Stmt\ClassMethod
    {
        return self::findCurrent($this->methodStack);
    }

    /**
     * @return list<string>
     */
    public function getSymbols(): array
    {
        if ($this->symbols !== null) {
            return $this->symbols;
        }

        $symbols = [];

        foreach ($this->declarations as [$class, $method]) {
            if ($class === null) {
                continue;
            }

            $name = FullyQualifiedClassNameManipulator::getFqcn($class);

            if ($name === null) {
                continue;
            }

            $symbols[] = $method === null
                ? $name->toString()
                : sprintf(
                    '%s::%s',
                    $name->toString(),
                    $method->name->toString(),
                );
        }

        return $this->symbols = $symbols;
    }

    /**
     * @template T of Node
     *
     * @param list<T> $nodes
     *
     * @return T|null
     */
    private static function findCurrent(array $nodes): ?Node
    {
        $key = array_key_last($nodes);

        if ($key === null) {
            return null;
        }

        return $nodes[$key];
    }
}
