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

use Infection\Configuration\SourceSymbolSelector;
use Infection\PhpParser\Visitor\FullyQualifiedClassNameManipulator;
use Infection\PhpParser\Visitor\ParentConnector;
use PhpParser\Node;
use function strcasecmp;

/**
 * Matches an enriched AST node against a class, optional method, and optional line.
 *
 * @internal
 */
final class SourceSymbolMatcher
{
    public function matches(Node $node, SourceSymbolSelector $selector): bool
    {
        $class = self::findAncestor($node, Node\Stmt\ClassLike::class);

        if (!$class instanceof Node\Stmt\ClassLike) {
            return false;
        }

        $className = FullyQualifiedClassNameManipulator::getFqcn($class);

        if ($className === null || strcasecmp($className->toString(), $selector->className) !== 0) {
            return false;
        }

        if ($selector->methodName !== null) {
            $method = self::findAncestor($node, Node\Stmt\ClassMethod::class);

            if (!$method instanceof Node\Stmt\ClassMethod || strcasecmp($method->name->toString(), $selector->methodName) !== 0) {
                return false;
            }
        }

        if ($selector->line === null) {
            return true;
        }

        return $node->getStartLine() <= $selector->line
            && $node->getEndLine() >= $selector->line;
    }

    /**
     * @param class-string<Node> $type
     */
    private static function findAncestor(Node $node, string $type): ?Node
    {
        do {
            if ($node instanceof $type) {
                return $node;
            }

            $node = ParentConnector::findParent($node);
        } while ($node !== null);

        return null;
    }
}
