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

namespace Infection\Mutator\Arithmetic;

use Generator;
use function in_array;
use Infection\Mutator\Util\Mutator;
use PhpParser\Node;

/**
 * @internal
 */
final class RoundingFamily extends Mutator
{
    private const MUTATORS_MAP = [
        'floor',
        'ceil',
        'round',
    ];

    /**
     * Mutates from one rounding function to all others:
     *     1. floor() to ceil() and round()
     *     2. ceil() to floor() and round()
     *     3. round() to ceil() and floor()
     *
     * @param Node&Node\Expr\FuncCall $node
     *
     * @return Generator
     */
    public function mutate(Node $node)
    {
        /** @var Node\Name $name */
        $name = $node->name;
        $currentFunctionName = $name->toLowerString();

        $mutateToFunctions = array_diff(self::MUTATORS_MAP, [$currentFunctionName]);

        foreach ($mutateToFunctions as $functionName) {
            yield new Node\Expr\FuncCall(
                new Node\Name($functionName),
                [$node->args[0]],
                $node->getAttributes()
            );
        }
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Expr\FuncCall) {
            return false;
        }

        if (!$node->name instanceof Node\Name ||
            !in_array($node->name->toLowerString(), self::MUTATORS_MAP, true)
        ) {
            return false;
        }

        return true;
    }
}
