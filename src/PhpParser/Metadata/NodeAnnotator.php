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

namespace Infection\PhpParser\Metadata;

use Error;
use Infection\CannotBeInstantiated;
use Infection\Reflection\ClassReflection;
use JsonSerializable;
use LogicException;
use PhpParser\Node;
use function Safe\json_encode;
use function sprintf;
use Stringable;
use Webmozart\Assert\Assert;

/**
 * Provides type-safe access to metadata annotations attached to PHP-Parser AST nodes.
 *
 * @internal
 */
final class NodeAnnotator
{
    use CannotBeInstantiated;

    public static function annotate(
        ?Node $node,
        Annotation $annotation,
        mixed $value = null,
    ): void {
        $node?->setAttribute($annotation->value, $value ?? true);
    }

    public static function getParent(Node $node): Node
    {
        self::assertNodeHasAttribute($node, Annotation::PARENT_NODE);

        return $node->getAttribute(Annotation::PARENT_NODE->value);
    }

    public static function findParent(Node $node): ?Node
    {
        return $node->getAttribute(Annotation::PARENT_NODE->value);
    }

    public static function hasNextNode(Node $node): bool
    {
        return $node->getAttribute(Annotation::NEXT_NODE->value) !== null;
    }

    public static function getFqcn(Node $node): ?Node\Name
    {
        // @phpstan-ignore property.notFound
        return $node->namespacedName
            ?? $node->getAttribute(Annotation::RESOLVED_NAME->value)
            ?? $node->getAttribute(Annotation::NAMESPACED_NAME->value);
    }

    public static function getResolvedName(Node $node): Node\Name
    {
        self::assertNodeHasAttribute($node, Annotation::RESOLVED_NAME);

        return $node->getAttribute(Annotation::RESOLVED_NAME->value);
    }

    public static function findReflectionClass(Node $node): ?ClassReflection
    {
        return $node->getAttribute(Annotation::REFLECTION_CLASS->value);
    }

    public static function findFunctionScope(Node $node): ?Node\FunctionLike
    {
        return $node->getAttribute(Annotation::FUNCTION_SCOPE->value);
    }

    public static function getFunctionScope(Node $node): Node\FunctionLike
    {
        self::assertNodeHasAttribute($node, Annotation::FUNCTION_SCOPE);

        return $node->getAttribute(Annotation::FUNCTION_SCOPE->value);
    }

    public static function isInsideFunction(Node $node): bool
    {
        return $node->hasAttribute(Annotation::IS_INSIDE_FUNCTION->value);
    }

    public static function isOnFunctionSignature(Node $node): bool
    {
        return $node->hasAttribute(Annotation::IS_ON_FUNCTION_SIGNATURE->value);
    }

    public static function getFunctionName(Node $node): string
    {
        return $node->getAttribute(Annotation::FUNCTION_NAME->value, '');
    }

    public static function areStrictTypesEnabled(Node\FunctionLike $node): ?bool
    {
        return $node->getAttribute(Annotation::IS_STRICT_TYPES->value);
    }

    private static function assertNodeHasAttribute(
        Node $node,
        Annotation $annotation,
    ): void {
        // Note: we do not use Assert here as it is a hotpath and computing a nicer error message
        // has a non-negligible impact.
        if (!$node->hasAttribute($annotation->value)) {
            throw new LogicException(
                sprintf(
                    'Expected to find the attribute "%s". Could not find it for the node: %s',
                    $annotation->value,
                    self::identifyNode($node),
                ),
            );
        }
    }

    private static function identifyNode(Node $node): string
    {
        // Note that various nodes implement JsonSerializable. This could be useful.
        // Unfortunately, in practice they often have cyclic references resulting
        // in recursion issues when attempting json_encode().
        if ($node instanceof Stringable) {
            return $node->__toString();
        }
        $nodeType = $node->getType();

        // Note that this will only be present for tests as AddIdToTraversedNodesVisitor
        // is not used otherwise.
        // As a result, we cannot use the AddIdToTraversedNodesVisitor::NODE_ID_ATTRIBUTE
        // constant here, neither can we expect the ID to be present.
        $nodeId = $node->getAttribute('nodeId', 'unknown ID');

        return sprintf(
            'Node(%s, %s)',
            $nodeType,
            $nodeId,
        );
    }
}
