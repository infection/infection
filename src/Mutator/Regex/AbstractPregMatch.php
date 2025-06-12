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

namespace Infection\Mutator\Regex;

use Generator;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\Mutator;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use function strtolower;

/**
 * @internal
 *
 * @implements Mutator<Node\Expr\FuncCall>
 */
abstract class AbstractPregMatch implements Mutator
{
    use GetMutatorName;

    /**
     * Replaces regex in "preg_match"
     *
     * @psalm-mutation-free
     *
     * @return iterable<FuncCall>
     */
    public function mutate(Node $node): iterable
    {
        if ($node->args[0] instanceof Node\VariadicPlaceholder) {
            return [];
        }

        $originalRegex = $this->pullOutRegex($node->args[0]);

        foreach ($this->mutateRegex($originalRegex) as $mutatedRegex) {
            $newArgument = $this->getNewRegexArgument($mutatedRegex, $node->args[0]);

            yield new FuncCall($node->name, [$newArgument] + $node->args, $node->getAttributes());
        }
    }

    public function canMutate(Node $node): bool
    {
        return $node instanceof FuncCall
            && $node->name instanceof Node\Name
            && strtolower((string) $node->name) === 'preg_match'
            && $node->args[0] instanceof Node\Arg
            && $node->args[0]->value instanceof Node\Scalar\String_
            && $this->isProperRegexToMutate($this->pullOutRegex($node->args[0]));
    }

    abstract protected function isProperRegexToMutate(string $regex): bool;

    /**
     * @psalm-mutation-free
     *
     * @return Generator<string>
     */
    abstract protected function mutateRegex(string $regex): Generator;

    /**
     * @psalm-mutation-free
     */
    private function pullOutRegex(Node\Arg $argument): string
    {
        /** @var Node\Scalar\String_ $stringNode */
        $stringNode = $argument->value;

        return $stringNode->value;
    }

    /**
     * @psalm-mutation-free
     */
    private function getNewRegexArgument(string $regex, Node\Arg $argument): Node\Arg
    {
        return new Node\Arg(
            new Node\Scalar\String_($regex, $argument->value->getAttributes()),
            $argument->byRef, $argument->unpack, $argument->getAttributes(),
        );
    }
}
