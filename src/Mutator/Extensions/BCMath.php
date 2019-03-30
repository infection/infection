<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2019, Maks Rafalko
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

namespace Infection\Mutator\Extensions;

use Generator;
use Infection\Mutator\Util\Mutator;
use Infection\Mutator\Util\MutatorConfig;
use PhpParser\Node;

/**
 * @internal
 */
final class BCMath extends Mutator
{
    private $converters;

    public function __construct(MutatorConfig $config)
    {
        parent::__construct($config);

        $settings = $this->getSettings();

        $this->setupConverters($settings);
    }

    /**
     * @param Node|Node\Expr\FuncCall $node
     *
     * @return Node|Node[]|Generator
     */
    public function mutate(Node $node)
    {
        yield from $this->converters[$node->name->toLowerString()]($node);
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof Node\Expr\FuncCall || !$node->name instanceof Node\Name) {
            return false;
        }

        $functionName = $node->name->toLowerString();

        return isset($this->converters[$functionName]) && \function_exists($functionName);
    }

    private function setupConverters(array $functionsMap): void
    {
        $converters = [
            'bcadd' => $this->mapCheckingMinArgs(2, $this->mapCastToString(
                $this->mapBinaryOperator(Node\Expr\BinaryOp\Plus::class)
            )),
            'bcdiv' => $this->mapCheckingMinArgs(2, $this->mapCastToString(
                $this->mapBinaryOperator(Node\Expr\BinaryOp\Div::class)
            )),
            'bcmod' => $this->mapCheckingMinArgs(2, $this->mapCastToString(
                $this->mapBinaryOperator(Node\Expr\BinaryOp\Mod::class)
            )),
            'bcmul' => $this->mapCheckingMinArgs(2, $this->mapCastToString(
                $this->mapBinaryOperator(Node\Expr\BinaryOp\Mul::class)
            )),
            'bcpow' => $this->mapCheckingMinArgs(2, $this->mapCastToString(
                $this->mapBinaryOperator(Node\Expr\BinaryOp\Pow::class)
            )),
            'bcsub' => $this->mapCheckingMinArgs(2, $this->mapCastToString(
                $this->mapBinaryOperator(Node\Expr\BinaryOp\Minus::class)
            )),
            'bcsqrt' => $this->mapCheckingMinArgs(1, $this->mapCastToString(
                $this->mapSquareRoots()
            )),
            'bcpowmod' => $this->mapCheckingMinArgs(3, $this->mapCastToString(
                $this->mapPowerModulo()
            )),
            'bccomp' => $this->mapCheckingMinArgs(2,
                $this->mapBinaryOperator(Node\Expr\BinaryOp\Spaceship::class)
            ),
        ];

        $functionsToRemove = \array_filter($functionsMap, static function ($isOn) {
            return !$isOn;
        });

        $this->converters = \array_diff_key($converters, $functionsToRemove);
    }

    private function mapCheckingMinArgs(int $minimumArgsCount, callable $converter)
    {
        return static function (Node\Expr\FuncCall $node) use ($minimumArgsCount, $converter): Generator {
            if (\count($node->args) >= $minimumArgsCount) {
                yield from $converter($node);
            }
        };
    }

    private function mapCastToString(callable $converter): callable
    {
        return static function (Node\Expr\FuncCall $node) use ($converter): Generator {
            foreach ($converter($node) as $newNode) {
                yield new Node\Expr\Cast\String_($newNode);
            }
        };
    }

    private function mapBinaryOperator(string $operator): callable
    {
        return static function (Node\Expr\FuncCall $node) use ($operator): Generator {
            yield new $operator($node->args[0]->value, $node->args[1]->value);
        };
    }

    private function mapSquareRoots(): callable
    {
        return static function (Node\Expr\FuncCall $node): Generator {
            yield new Node\Expr\FuncCall(new Node\Name('\sqrt'), [$node->args[0]]);
        };
    }

    private function mapPowerModulo(): callable
    {
        return static function (Node\Expr\FuncCall $node): Generator {
            yield new Node\Expr\BinaryOp\Mod(
                new Node\Expr\FuncCall(
                    new Node\Name('\pow'),
                    [$node->args[0], $node->args[1]]
                ),
                $node->args[2]->value
            );
        };
    }
}
