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

namespace Infection\PhpParser;

use DomainException;
use function get_class;
use InvalidArgumentException;
use function krsort;
use PhpParser\Node;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use function Safe\sprintf;
use const SORT_NUMERIC;

/**
 * @internal
 * @final
 */
class PrioritizedVisitorsNodeTraverser implements NodeTraverserInterface
{
    /**
     * @var array<int, NodeVisitor>
     */
    private $visitors = [];

    private $traverser;

    public function __construct(NodeTraverserInterface $decoratedTraverser)
    {
        $this->traverser = $decoratedTraverser;
    }

    public function addPrioritizedVisitor(NodeVisitor $visitor, int $priority): void
    {
        if (array_key_exists($priority, $this->visitors)) {
            throw new InvalidArgumentException(sprintf(
                'The priority "%d" is already used for the visitor "%s". Please use a different one',
                $priority,
                get_class($this->visitors[$priority])
            ));
        }

        $this->visitors[$priority] = $visitor;

        // This could on theory be optimized by doing it only once. However for now we need
        // the `getVisitors()` method for inspecting the visitors state.
        krsort($this->visitors, SORT_NUMERIC);
    }

    public function addVisitor(NodeVisitor $visitor): void
    {
        throw new DomainException('Add a non-prioritized visitor is not supported.');
    }

    public function removeVisitor(NodeVisitor $visitor): void
    {
        throw new DomainException('Removing a visitor is not supported.');
    }

    /**
     * Warning: should be used for test purposes only
     *
     * @return array<int, NodeVisitor>
     */
    public function getVisitors(): array
    {
        return $this->visitors;
    }

    /**
     * @param Node[] $nodes
     *
     * @return Node[]
     */
    public function traverse(array $nodes): array
    {
        foreach ($this->visitors as $visitor) {
            $this->traverser->addVisitor($visitor);
        }

        return $this->traverser->traverse($nodes);
    }
}
