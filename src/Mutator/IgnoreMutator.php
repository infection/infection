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

namespace Infection\Mutator;

use DomainException;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use Infection\Reflection\ClassReflection;
use PhpParser\Node;
use function sprintf;

/**
 * The mutators implement the ignore + canMutator pattern. The downside of this pattern is that
 * it makes its public API more complex and easier to mess up since the caller needs to be careful
 * of checking if he should mutate before attempting to mutate.
 *
 * A better alternative would be to allow to blindly mutate and do this "ignore + should mutate"
 * check internally. We however do not do so because before actually mutating, there is a few
 * expansive steps (e.g. retrieving the tests methods). Hence the currently chosen pattern allows
 * better performance optimization in our case.
 *
 * @internal
 *
 * @template TNode of Node
 * @implements Mutator<TNode>
 */
final readonly class IgnoreMutator implements Mutator
{
    /**
     * @param Mutator<TNode> $mutator
     */
    public function __construct(private IgnoreConfig $config, private Mutator $mutator)
    {
    }

    public static function getDefinition(): Definition
    {
        // Since we do not use `getDefinition()` in our source code yet (only in tests for
        // documentation purposes), we do not worry about this one for now. If needed, this one
        // can also be made non-static to return the definition of the decorated mutator.
        throw new DomainException(sprintf(
            'The class "%s" does not have a definition',
            self::class,
        ));
    }

    public function canMutate(Node $node): bool
    {
        if (!$this->mutator->canMutate($node)) {
            return false;
        }

        /** @var ClassReflection|null $reflectionClass */
        $reflectionClass = $node->getAttribute(ReflectionVisitor::REFLECTION_CLASS_KEY);

        if (!$reflectionClass instanceof ClassReflection) {
            return true;
        }

        return !$this->config->isIgnored(
            $reflectionClass->getName(),
            $node->getAttribute(ReflectionVisitor::FUNCTION_NAME, ''),
            $node->getStartLine(),
        );
    }

    /**
     * @psalm-mutation-free
     *
     * @param TNode $node
     *
     * @return iterable<int|Node|Node[]>
     */
    public function mutate(Node $node): iterable
    {
        return $this->mutator->mutate($node);
    }

    public function getName(): string
    {
        return $this->mutator->getName();
    }
}
