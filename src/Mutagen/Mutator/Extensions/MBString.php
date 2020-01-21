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

namespace Infection\Mutagen\Mutator\Extensions;

use function array_diff_key;
use function array_filter;
use function array_slice;
use function constant;
use function count;
use function defined;
use Generator;
use Infection\Mutagen\Mutator\Definition;
use Infection\Mutagen\Mutator\GetMutatorName;
use Infection\Mutagen\Mutator\Mutator;
use Infection\Mutagen\Mutator\MutatorCategory;
use Infection\Mutagen\Mutator\Util\MutatorConfig;
use PhpParser\Node;

/**
 * @internal
 */
final class MBString implements Mutator
{
    use GetMutatorName;

    private $converters;

    public function __construct(MutatorConfig $config)
    {
        $settings = $config->getMutatorSettings();

        $this->setupConverters($settings);
    }

    public static function getDefinition(): ?Definition
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
            null
        );
    }

    /**
     * @param Node\Expr\FuncCall $node
     *
     * @return Generator<Node\Expr\FuncCall>
     */
    public function mutate(Node $node): Generator
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

    private function setupConverters(array $functionsMap): void
    {
        $converters = [
            'mb_chr' => $this->makeFunctionAndRemoveExtraArgsMapper('chr', 1),
            'mb_ord' => $this->makeFunctionAndRemoveExtraArgsMapper('ord', 1),
            'mb_parse_str' => $this->makeFunctionMapper('parse_str'),
            'mb_send_mail' => $this->makeFunctionMapper('mail'),
            'mb_strcut' => $this->makeFunctionAndRemoveExtraArgsMapper('substr', 3),
            'mb_stripos' => $this->makeFunctionAndRemoveExtraArgsMapper('stripos', 3),
            'mb_stristr' => $this->makeFunctionAndRemoveExtraArgsMapper('stristr', 3),
            'mb_strlen' => $this->makeFunctionAndRemoveExtraArgsMapper('strlen', 1),
            'mb_strpos' => $this->makeFunctionAndRemoveExtraArgsMapper('strpos', 3),
            'mb_strrchr' => $this->makeFunctionAndRemoveExtraArgsMapper('strrchr', 2),
            'mb_strripos' => $this->makeFunctionAndRemoveExtraArgsMapper('strripos', 3),
            'mb_strrpos' => $this->makeFunctionAndRemoveExtraArgsMapper('strrpos', 3),
            'mb_strstr' => $this->makeFunctionAndRemoveExtraArgsMapper('strstr', 3),
            'mb_strtolower' => $this->makeFunctionAndRemoveExtraArgsMapper('strtolower', 1),
            'mb_strtoupper' => $this->makeFunctionAndRemoveExtraArgsMapper('strtoupper', 1),
            'mb_str_split' => $this->makeFunctionAndRemoveExtraArgsMapper('str_split', 2),
            'mb_substr_count' => $this->makeFunctionAndRemoveExtraArgsMapper('substr_count', 2),
            'mb_substr' => $this->makeFunctionAndRemoveExtraArgsMapper('substr', 3),
            'mb_convert_case' => $this->makeConvertCaseMapper(),
        ];

        $functionsToRemove = array_filter($functionsMap, static function (bool $isOn): bool {
            return !$isOn;
        });

        $this->converters = array_diff_key($converters, $functionsToRemove);
    }

    private function makeFunctionMapper(string $newFunctionName): callable
    {
        return function (Node\Expr\FuncCall $node) use ($newFunctionName): Generator {
            yield $this->mapFunctionCall($node, $newFunctionName, $node->args);
        };
    }

    private function makeFunctionAndRemoveExtraArgsMapper(string $newFunctionName, int $argsAtMost): callable
    {
        return function (Node\Expr\FuncCall $node) use ($newFunctionName, $argsAtMost): Generator {
            yield $this->mapFunctionCall($node, $newFunctionName, array_slice($node->args, 0, $argsAtMost));
        };
    }

    private function makeConvertCaseMapper(): callable
    {
        return function (Node\Expr\FuncCall $node): Generator {
            $modeValue = $this->getConvertCaseModeValue($node);

            if ($modeValue === null) {
                return;
            }

            $functionName = $this->getConvertCaseFunctionName($modeValue);

            if ($functionName === null) {
                return;
            }

            yield $this->mapFunctionCall($node, $functionName, [$node->args[0]]);
        };
    }

    private function getConvertCaseModeValue(Node\Expr\FuncCall $node): ?int
    {
        if (count($node->args) < 2) {
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

    private function getConvertCaseFunctionName(int $mode): ?string
    {
        if ($this->isInMbCaseMode($mode, 'MB_CASE_UPPER', 'MB_CASE_UPPER_SIMPLE')) {
            return 'strtoupper';
        }

        if ($this->isInMbCaseMode($mode, 'MB_CASE_LOWER', 'MB_CASE_LOWER_SIMPLE', 'MB_CASE_FOLD', 'MB_CASE_FOLD_SIMPLE')) {
            return 'strtolower';
        }

        if ($this->isInMbCaseMode($mode, 'MB_CASE_TITLE', 'MB_CASE_TITLE_SIMPLE')) {
            return 'ucwords';
        }

        return null;
    }

    private function isInMbCaseMode(int $mode, string ...$cases): bool
    {
        foreach ($cases as $constant) {
            if (defined($constant) && constant($constant) === $mode) {
                return true;
            }
        }

        return false;
    }

    private function mapFunctionCall(Node\Expr\FuncCall $node, string $newFuncName, array $args): Node\Expr\FuncCall
    {
        return new Node\Expr\FuncCall(
            new Node\Name($newFuncName, $node->name->getAttributes()),
            $args,
            $node->getAttributes()
        );
    }
}
