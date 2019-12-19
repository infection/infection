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

namespace Infection\Mutator\Extensions;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Expr\BinaryOp\Plus;
use PhpParser\Node\Expr\BinaryOp\Div;
use PhpParser\Node\Expr\BinaryOp\Mod;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\BinaryOp\Pow;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Spaceship;
use PhpParser\Node\Expr\Cast\String_;
use function array_diff_key;
use function array_filter;
use function count;
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
     * @param Node&Node\Expr\FuncCall $node
     *
     * @return Generator<Node|array<Node>>
     */
    public function mutate(Node $node)
    {
        /** @var Node\Name $name */
        $name = $node->name;

        yield from $this->converters[$name->toLowerString()]($node);
    }

    protected function mutatesNode(Node $node): bool
    {
        if (!$node instanceof FuncCall || !$node->name instanceof Name) {
            return false;
        }

        return isset($this->converters[$node->name->toLowerString()]);
    }

    private function setupConverters(array $functionsMap): void
    {
        $converters = [
            'bcadd' => $this->makeCheckingMinArgsMapper(2, $this->makeCastToStringMapper(
                $this->makeBinaryOperatorMapper(Plus::class)
            )),
            'bcdiv' => $this->makeCheckingMinArgsMapper(2, $this->makeCastToStringMapper(
                $this->makeBinaryOperatorMapper(Div::class)
            )),
            'bcmod' => $this->makeCheckingMinArgsMapper(2, $this->makeCastToStringMapper(
                $this->makeBinaryOperatorMapper(Mod::class)
            )),
            'bcmul' => $this->makeCheckingMinArgsMapper(2, $this->makeCastToStringMapper(
                $this->makeBinaryOperatorMapper(Mul::class)
            )),
            'bcpow' => $this->makeCheckingMinArgsMapper(2, $this->makeCastToStringMapper(
                $this->makeBinaryOperatorMapper(Pow::class)
            )),
            'bcsub' => $this->makeCheckingMinArgsMapper(2, $this->makeCastToStringMapper(
                $this->makeBinaryOperatorMapper(Minus::class)
            )),
            'bcsqrt' => $this->makeCheckingMinArgsMapper(1, $this->makeCastToStringMapper(
                $this->makeSquareRootsMapper()
            )),
            'bcpowmod' => $this->makeCheckingMinArgsMapper(3, $this->makeCastToStringMapper(
                $this->makePowerModuloMapper()
            )),
            'bccomp' => $this->makeCheckingMinArgsMapper(2,
                $this->makeBinaryOperatorMapper(Spaceship::class)
            ),
        ];

        $functionsToRemove = array_filter($functionsMap, static function (bool $isOn): bool {
            return !$isOn;
        });

        $this->converters = array_diff_key($converters, $functionsToRemove);
    }

    private function makeCheckingMinArgsMapper(int $minimumArgsCount, callable $converter)
    {
        return static function (FuncCall $node) use ($minimumArgsCount, $converter): Generator {
            if (count($node->args) >= $minimumArgsCount) {
                yield from $converter($node);
            }
        };
    }

    private function makeCastToStringMapper(callable $converter): callable
    {
        return static function (FuncCall $node) use ($converter): Generator {
            foreach ($converter($node) as $newNode) {
                yield new String_($newNode);
            }
        };
    }

    private function makeBinaryOperatorMapper(string $operator): callable
    {
        return static function (FuncCall $node) use ($operator): Generator {
            yield new $operator($node->args[0]->value, $node->args[1]->value);
        };
    }

    private function makeSquareRootsMapper(): callable
    {
        return static function (FuncCall $node): Generator {
            yield new FuncCall(new Name('\sqrt'), [$node->args[0]]);
        };
    }

    private function makePowerModuloMapper(): callable
    {
        return static function (FuncCall $node): Generator {
            yield new Mod(
                new FuncCall(
                    new Name('\pow'),
                    [$node->args[0], $node->args[1]]
                ),
                $node->args[2]->value
            );
        };
    }
}
