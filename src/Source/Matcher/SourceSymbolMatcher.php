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

namespace Infection\Source\Matcher;

use Infection\Configuration\SourceSymbol\SourceSymbolSelector;
use Infection\PhpParser\Visitor\FullyQualifiedClassNameManipulator;
use PhpParser\Node;
use function str_contains;
use function strcasecmp;

/**
 * Matches an enriched AST node against a class, optional method, and optional line.
 *
 * @internal
 */
final class SourceSymbolMatcher
{
    /**
     * @var array<int, true>
     */
    private array $matchedSelectors = [];

    /**
     * @param list<SourceSymbolSelector> $selectors
     */
    public function __construct(
        private readonly array $selectors,
    ) {
    }

    public function matches(
        Node $node,
        ?Node\Stmt\ClassLike $class,
        ?Node\Stmt\ClassMethod $method,
    ): bool {
        $matches = false;

        foreach ($this->selectors as $index => $selector) {
            if (!$this->matchesSelector($node, $class, $method, $selector)) {
                continue;
            }

            $this->matchedSelectors[$index] = true;
            $matches = true;
        }

        return $matches;
    }

    public function hasSelectors(): bool
    {
        return $this->selectors !== [];
    }

    /**
     * @return list<SourceSymbolSelector>
     */
    public function getUnmatchedSelectors(): array
    {
        $unmatched = [];

        foreach ($this->selectors as $index => $selector) {
            if (!isset($this->matchedSelectors[$index])) {
                $unmatched[] = $selector;
            }
        }

        return $unmatched;
    }

    public function reset(): void
    {
        $this->matchedSelectors = [];
    }

    private function matchesSelector(
        Node $node,
        ?Node\Stmt\ClassLike $class,
        ?Node\Stmt\ClassMethod $method,
        SourceSymbolSelector $selector,
    ): bool {
        if ($class === null) {
            return false;
        }

        $shortClassName = $class->name;

        if ($shortClassName === null) {
            return false;
        }

        $className = FullyQualifiedClassNameManipulator::getFqcn($class);

        if (
            $className === null
            || !$this->classMatches($shortClassName, $className, $selector)
        ) {
            return false;
        }

        if ($selector->methodName !== null) {
            if (
                $method === null
                || strcasecmp($method->name->toString(), $selector->methodName) !== 0
            ) {
                return false;
            }
        }

        if ($selector->line === null) {
            return true;
        }

        return $node->getStartLine() <= $selector->line
            && $node->getEndLine() >= $selector->line;
    }

    private function classMatches(
        Node\Identifier $shortClassName,
        Node\Name $className,
        SourceSymbolSelector $selector,
    ): bool {
        return str_contains($selector->className, '\\')
            ? strcasecmp($className->toString(), $selector->className) === 0
            : strcasecmp($shortClassName->toString(), $selector->className) === 0;
    }
}
