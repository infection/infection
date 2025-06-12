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
use function array_key_exists;
use function array_slice;
use Closure;
use function constant;
use function count;
use function defined;
use Generator;
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
final class MBString implements ConfigurableMutator
{
    use GetConfigClassName;
    use GetMutatorName;

    /**
     * @var array<string, Closure(Node\Expr\FuncCall): iterable<Node\Expr\FuncCall>>
     */
    private readonly array $converters;

    public function __construct(MBStringConfig $config)
    {
        $this->converters = self::createConverters($config->getAllowedFunctions());
    }

    public static function getDefinition(): Definition
    {
        return new Definition(
            <<<'TXT'
                Replaces a statement making use of the mbstring extension with its vanilla code equivalent. For
                example:

                ```php
                $x = mb_strlen($str) < 10;
                ```

                Will be mutated to:

                ```php
                $x = strlen($str) < 10;
                ```
                TXT
            ,
            MutatorCategory::SEMANTIC_REDUCTION,
            null,
            <<<'DIFF'
                - $x = mb_strlen($str) < 10;
                + $x = strlen($str) < 10;
                DIFF,
        );
    }

    /**
     * @psalm-mutation-free
     * @psalm-suppress ImpureMethodCall
     *
     * @return iterable<Node\Expr\FuncCall>
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

        return array_key_exists($node->name->toLowerString(), $this->converters);
    }

    /**
     * @param string[] $allowedFunctions
     *
     * @return array<string, Closure>
     */
    private static function createConverters(array $allowedFunctions): array
    {
        return array_intersect_key(
            [
                'mb_chr' => self::makeFunctionAndRemoveExtraArgsMapper('chr', 1),
                'mb_ord' => self::makeFunctionAndRemoveExtraArgsMapper('ord', 1),
                'mb_parse_str' => self::makeFunctionMapper('parse_str'),
                'mb_send_mail' => self::makeFunctionMapper('mail'),
                'mb_strcut' => self::makeFunctionAndRemoveExtraArgsMapper('substr', 3),
                'mb_stripos' => self::makeFunctionAndRemoveExtraArgsMapper('stripos', 3),
                'mb_stristr' => self::makeFunctionAndRemoveExtraArgsMapper('stristr', 3),
                'mb_strlen' => self::makeFunctionAndRemoveExtraArgsMapper('strlen', 1),
                'mb_strpos' => self::makeFunctionAndRemoveExtraArgsMapper('strpos', 3),
                'mb_strrchr' => self::makeFunctionAndRemoveExtraArgsMapper('strrchr', 2),
                'mb_strripos' => self::makeFunctionAndRemoveExtraArgsMapper('strripos', 3),
                'mb_strrpos' => self::makeFunctionAndRemoveExtraArgsMapper('strrpos', 3),
                'mb_strstr' => self::makeFunctionAndRemoveExtraArgsMapper('strstr', 3),
                'mb_strtolower' => self::makeFunctionAndRemoveExtraArgsMapper('strtolower', 1),
                'mb_strtoupper' => self::makeFunctionAndRemoveExtraArgsMapper('strtoupper', 1),
                'mb_str_split' => self::makeFunctionAndRemoveExtraArgsMapper('str_split', 2),
                'mb_substr_count' => self::makeFunctionAndRemoveExtraArgsMapper('substr_count', 2),
                'mb_substr' => self::makeFunctionAndRemoveExtraArgsMapper('substr', 3),
                'mb_convert_case' => self::makeConvertCaseMapper(),
            ],
            array_fill_keys($allowedFunctions, null),
        );
    }

    /**
     * @return Closure(Node\Expr\FuncCall): iterable<Node\Expr\FuncCall>
     */
    private static function makeFunctionMapper(string $newFunctionName): Closure
    {
        return static function (Node\Expr\FuncCall $node) use ($newFunctionName): iterable {
            yield self::mapFunctionCall($node, $newFunctionName, $node->args);
        };
    }

    /**
     * @return Closure(Node\Expr\FuncCall): iterable<Node\Expr\FuncCall>
     */
    private static function makeFunctionAndRemoveExtraArgsMapper(string $newFunctionName, int $argsAtMost): Closure
    {
        return static function (Node\Expr\FuncCall $node) use ($newFunctionName, $argsAtMost): iterable {
            yield self::mapFunctionCall($node, $newFunctionName, array_slice($node->args, 0, $argsAtMost));
        };
    }

    /**
     * @return Closure(Node\Expr\FuncCall): iterable<Node\Expr\FuncCall>
     */
    private static function makeConvertCaseMapper(): Closure
    {
        return static function (Node\Expr\FuncCall $node): Generator { // PHPStan can't infer this from yield
            $modeValue = self::getConvertCaseModeValue($node);

            if ($modeValue === null) {
                return;
            }

            $functionName = self::getConvertCaseFunctionName($modeValue);

            if ($functionName === null) {
                return;
            }

            yield self::mapFunctionCall($node, $functionName, [$node->args[0]]);
        };
    }

    private static function getConvertCaseModeValue(Node\Expr\FuncCall $node): ?int
    {
        if (count($node->args) < 2) {
            return null;
        }

        if ($node->args[1] instanceof Node\VariadicPlaceholder) {
            return null;
        }

        $mode = $node->args[1]->value;

        if ($mode instanceof Node\Scalar\LNumber) {
            return $mode->value;
        }

        if ($mode instanceof Node\Expr\ConstFetch) {
            return constant($mode->name->toString());
        }

        return null;
    }

    private static function getConvertCaseFunctionName(int $mode): ?string
    {
        if (self::isInMbCaseMode($mode, 'MB_CASE_UPPER', 'MB_CASE_UPPER_SIMPLE')) {
            return 'strtoupper';
        }

        if (self::isInMbCaseMode($mode, 'MB_CASE_LOWER', 'MB_CASE_LOWER_SIMPLE', 'MB_CASE_FOLD', 'MB_CASE_FOLD_SIMPLE')) {
            return 'strtolower';
        }

        if (self::isInMbCaseMode($mode, 'MB_CASE_TITLE', 'MB_CASE_TITLE_SIMPLE')) {
            return 'ucwords';
        }

        return null;
    }

    private static function isInMbCaseMode(int $mode, string ...$cases): bool
    {
        foreach ($cases as $constant) {
            if (defined($constant) && constant($constant) === $mode) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<Node\Arg|Node\VariadicPlaceholder> $args
     */
    private static function mapFunctionCall(Node\Expr\FuncCall $node, string $newFuncName, array $args): Node\Expr\FuncCall
    {
        return new Node\Expr\FuncCall(
            new Node\Name($newFuncName, $node->name->getAttributes()),
            $args,
            $node->getAttributes(),
        );
    }
}
