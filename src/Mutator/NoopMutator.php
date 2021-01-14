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
use PhpParser\Node;
use function Safe\sprintf;

/**
 * @internal
 *
 * @template TNode of Node
 * @implements Mutator<TNode>
 */
final class NoopMutator implements Mutator
{
    /** @var Mutator<TNode> */
    private Mutator $mutator;

    /**
     * @param Mutator<TNode> $mutator
     */
    public function __construct(Mutator $mutator)
    {
        $this->mutator = $mutator;
    }

    public static function getDefinition(): ?Definition
    {
        throw new DomainException(sprintf(
            'The class "%s" does not have a definition',
            self::class
        ));
    }

    public function canMutate(Node $node): bool
    {
        return $this->mutator->canMutate($node);
    }

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node|Node[]>
     */
    public function mutate(Node $node): iterable
    {
        yield $node;
    }

    public function getName(): string
    {
        return $this->mutator->getName();
    }
}
