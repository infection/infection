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

use function array_fill_keys;
use function array_intersect_key;
use Closure;
use function count;
use Infection\Mutator\ConfigurableMutator;
use Infection\Mutator\Definition;
use Infection\Mutator\GetConfigClassName;
use Infection\Mutator\GetMutatorName;
use Infection\Mutator\MutatorCategory;
use PhpParser\Node;

/**
 * @internal
 *
 * @implements ConfigurableMutator<Node\Expr\FuncCall>
 */
final class BCMath implements ConfigurableMutator
{
    use GetConfigClassName;
    use GetMutatorName;

    /**
     * @var array<string, Closure(Node\Expr\FuncCall): iterable<Node\Expr>>
     */
    private array $converters;

    public function __construct(BCMathConfig $config)
    {
        $this->converters = self::createConverters($config->getAllowedFunctions());
    }

    public static function getDefinition(): Definition
    {
        return new Definition(
            <<<'TXT'
                Replaces a statement making use of the bcmath extension with its vanilla code equivalent. For example:

                ```php`
                $x = bcadd($a, $b);
                ```

                Will be mutated to:

                ```php
                $x = (string) ($a + $b);
                ```
                TXT
            ,
            MutatorCategory::SEMANTIC_REDUCTION,
            null,
            <<<'DIFF'
                - $x = bcadd($a, $b);
                + $x = (string) ($a + $b);
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     * @psalm-suppress ImpureMethodCall
     *
     * @return iterable<Node\Expr>
     */
    public function mutate(Node $node): iterable
    {
        /** @var Node\Name $name */
        $name = $node->name;

        yield from $this->converters[$name->toLowerString()]($node);
    }

    public function canMutate(Node $node): bool
    {
        if (!$node instanceof Node\Expr\FuncCall || !$node->name instanceof Node\Name) {
            return false;
        }

        return isset($this->converters[$node->name->toLowerString()]);
    }

    /**
     * @param string[] $functionsMap
     *
     * @return array<string, Closure(Node\Expr\FuncCall): iterable<Node\Expr>>
     */
    private static function createConverters(array $functionsMap): array
    {
        return array_intersect_key(
            [
                'bcadd' => self::makeCheckingMinArgsMapper(
                    2,
                    self::makeCastToStringMapper(
                        self::makeBinaryOperatorMapper(Node\Expr\BinaryOp\Plus::class),
                    ),
                ),
                'bcdiv' => self::makeCheckingMinArgsMapper(
                    2,
                    self::makeCastToStringMapper(
                        self::makeBinaryOperatorMapper(Node\Expr\BinaryOp\Div::class),
                    ),
                ),
                'bcmod' => self::makeCheckingMinArgsMapper(
                    2,
                    self::makeCastToStringMapper(
                        self::makeBinaryOperatorMapper(Node\Expr\BinaryOp\Mod::class),
                    ),
                ),
                'bcmul' => self::makeCheckingMinArgsMapper(
                    2,
                    self::makeCastToStringMapper(
                        self::makeBinaryOperatorMapper(Node\Expr\BinaryOp\Mul::class),
                    ),
                ),
                'bcpow' => self::makeCheckingMinArgsMapper(
                    2,
                    self::makeCastToStringMapper(
                        self::makeBinaryOperatorMapper(Node\Expr\BinaryOp\Pow::class),
                    ),
                ),
                'bcsub' => self::makeCheckingMinArgsMapper(
                    2,
                    self::makeCastToStringMapper(
                        self::makeBinaryOperatorMapper(Node\Expr\BinaryOp\Minus::class),
                    ),
                ),
                'bcsqrt' => self::makeCheckingMinArgsMapper(
                    1,
                    self::makeCastToStringMapper(
                        self::makeSquareRootsMapper(),
                    ),
                ),
                'bcpowmod' => self::makeCheckingMinArgsMapper(
                    3,
                    self::makeCastToStringMapper(
                        self::makePowerModuloMapper(),
                    ),
                ),
                'bccomp' => self::makeCheckingMinArgsMapper(
                    2,
                    self::makeBinaryOperatorMapper(Node\Expr\BinaryOp\Spaceship::class),
                ),
            ],
            array_fill_keys($functionsMap, null),
        );
    }

    /**
     * @param Closure(Node\Expr\FuncCall): iterable<Node\Expr> $converter
     *
     * @return Closure(Node\Expr\FuncCall): iterable<Node\Expr>
     */
    private static function makeCheckingMinArgsMapper(int $minimumArgsCount, Closure $converter): Closure
    {
        return static function (Node\Expr\FuncCall $node) use ($minimumArgsCount, $converter): iterable {
            if (count($node->args) >= $minimumArgsCount) {
                yield from $converter($node);
            }
        };
    }

    /**
     * @param Closure(Node\Expr\FuncCall): iterable<Node\Expr> $converter
     *
     * @return Closure(Node\Expr\FuncCall): iterable<Node\Expr\Cast\String_>
     */
    private static function makeCastToStringMapper(Closure $converter): Closure
    {
        return static function (Node\Expr\FuncCall $node) use ($converter): iterable {
            foreach ($converter($node) as $newNode) {
                yield new Node\Expr\Cast\String_($newNode);
            }
        };
    }

    /**
     * @phpstan-param class-string<Node\Expr\BinaryOp> $operator
     *
     * @return Closure(Node\Expr\FuncCall): iterable<Node\Expr\BinaryOp>
     */
    private static function makeBinaryOperatorMapper(string $operator): Closure
    {
        return static function (Node\Expr\FuncCall $node) use ($operator): iterable {
            if ($node->args[0] instanceof Node\VariadicPlaceholder || $node->args[1] instanceof Node\VariadicPlaceholder) {
                return;
            }

            yield new $operator($node->args[0]->value, $node->args[1]->value);
        };
    }

    /**
     * @return Closure(Node\Expr\FuncCall): iterable<Node\Expr\FuncCall>
     */
    private static function makeSquareRootsMapper(): Closure
    {
        return static function (Node\Expr\FuncCall $node): iterable {
            yield new Node\Expr\FuncCall(new Node\Name('\sqrt'), [$node->args[0]]);
        };
    }

    /**
     * @return Closure(Node\Expr\FuncCall): iterable<Node\Expr\BinaryOp\Mod>
     */
    private static function makePowerModuloMapper(): Closure
    {
        return static function (Node\Expr\FuncCall $node): iterable {
            if ($node->args[2] instanceof Node\VariadicPlaceholder) {
                return;
            }

            yield new Node\Expr\BinaryOp\Mod(
                new Node\Expr\FuncCall(
                    new Node\Name('\pow'),
                    [$node->args[0], $node->args[1]],
                ),
                $node->args[2]->value,
            );
        };
    }
}
