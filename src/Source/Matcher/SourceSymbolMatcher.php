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
use function ltrim;
use PhpParser\Node;
use function str_contains;
use function str_ends_with;
use function strcasecmp;
use function strlen;
use function strncasecmp;

/**
 * Matches an enriched AST node against source symbol selectors.
 *
 * @internal
 */
final readonly class SourceSymbolMatcher
{
    /**
     * @param list<SourceSymbolSelector> $selectors
     */
    public function __construct(
        private array $selectors,
    ) {
    }

    /**
     * The caller supplies the class and method context because $node alone does not
     * identify the declarations it belongs to. For a class-like or method node,
     * the corresponding context is $node itself.
     */
    public function matches(
        Node $node,
        ?Node\Stmt\ClassLike $class,
        ?Node\Stmt\ClassMethod $method,
    ): bool {
        foreach ($this->selectors as $selector) {
            $matches = self::matchesSelector(
                $node,
                $class,
                $method,
                $selector,
            );

            if ($matches) {
                return true;
            }
        }

        return false;
    }

    public function hasSelectors(): bool
    {
        return $this->selectors !== [];
    }

    private static function matchesSelector(
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

        return self::classMatches($shortClassName, $className, $selector)
            && self::methodMatches($method, $selector)
            && self::lineMatches($node, $selector);
    }

    private static function classMatches(
        Node\Identifier $shortClassName,
        ?Node\Name $className,
        SourceSymbolSelector $selector,
    ): bool {
        if ($className === null) {
            return false;
        }

        if (!str_contains($selector->className, '\\')) {
            return self::equalsIgnoringCase(
                $shortClassName->toString(),
                $selector->className,
            );
        }

        $selectedClassName = ltrim($selector->className, '\\');

        if (str_ends_with($selectedClassName, '\\')) {
            return self::startsWithIgnoringCase(
                $className->toString(),
                $selectedClassName,
            );
        }

        return self::equalsIgnoringCase(
            $className->toString(),
            $selectedClassName,
        );
    }

    private static function methodMatches(
        ?Node\Stmt\ClassMethod $method,
        SourceSymbolSelector $selector,
    ): bool {
        if ($selector->methodName === null) {
            return true;
        }

        if ($method === null) {
            return false;
        }

        return self::equalsIgnoringCase(
            $method->name->toString(),
            $selector->methodName,
        );
    }

    private static function lineMatches(
        Node $node,
        SourceSymbolSelector $selector,
    ): bool {
        if ($selector->line === null) {
            return true;
        }

        return $node->getStartLine() > $selector->line
            ? false
            : $node->getEndLine() >= $selector->line;
    }

    private static function equalsIgnoringCase(
        string $left,
        string $right,
    ): bool {
        return strcasecmp(
            $left,
            $right,
        ) === 0;
    }

    private static function startsWithIgnoringCase(
        string $value,
        string $prefix,
    ): bool {
        return strncasecmp(
            $value,
            $prefix,
            strlen($prefix),
        ) === 0;
    }
}
