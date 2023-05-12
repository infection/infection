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

namespace Infection\Mutator\Unwrap;

use function array_key_exists;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use PhpParser\Node;
use function strtolower;

/**
 * @internal
 *
 * @implements Mutator<Node\Expr\FuncCall>
 */
abstract class AbstractFunctionUnwrapMutator implements Mutator
{
    use GetMutatorName;

    /**
     * @psalm-mutation-free
     *
     * @return iterable<Node\Expr>
     */
    final public function mutate(Node $node): iterable
    {
        foreach ($this->getParameterIndexes($node) as $index) {
            if ($node->args[$index] instanceof Node\VariadicPlaceholder) {
                continue;
            }

            if ($node->args[$index]->unpack) {
                continue;
            }

            yield $node->args[$index]->value;
        }
    }

    final public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Expr\FuncCall || !$node->name instanceof Node\Name) {
            return false;
        }

        foreach ($this->getParameterIndexes($node) as $index) {
            if (!array_key_exists($index, $node->args)) {
                return false;
            }
        }

        return $node->name->toLowerString() === strtolower($this->getFunctionName());
    }

    abstract protected function getFunctionName(): string;

    /**
     * @psalm-mutation-free
     *
     * @return iterable<int>
     */
    protected function getParameterIndexes(Node\Expr\FuncCall $node): iterable
    {
        yield 0;
    }
}
