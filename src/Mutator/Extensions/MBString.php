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
final class MBString extends Mutator
{
    private const MB_CASES = [
        'MB_CASE_UPPER' => 0,
        'MB_CASE_LOWER' => 1,
        'MB_CASE_TITLE' => 2,
        'MB_CASE_FOLD' => 3,
        'MB_CASE_UPPER_SIMPLE' => 4,
        'MB_CASE_LOWER_SIMPLE' => 5,
        'MB_CASE_TITLE_SIMPLE' => 6,
        'MB_CASE_FOLD_SIMPLE' => 7,
    ];

    private $converters;

    public function __construct(MutatorConfig $config)
    {
        parent::__construct($config);

        $settings = $this->getSettings();

        $this->setupConverters($settings);
    }

    /**
     * @return Node|Node[]|Generator
     */
    public function mutate(Node $node)
    {
        yield from $this->converters[$this->getFunctionName($node)]($node);
    }

    protected function mutatesNode(Node $node): bool
    {
        return isset($this->converters[$this->getFunctionName($node)]);
    }

    private function setupConverters(array $functionsMap): void
    {
        $converters = [
            'mb_chr' => $this->mapNameSkipArg('chr', 1),
            'mb_ord' => $this->mapNameSkipArg('ord', 1),
            'mb_parse_str' => $this->mapName('parse_str'),
            'mb_send_mail' => $this->mapName('mail'),
            'mb_strcut' => $this->mapNameSkipArg('substr', 3),
            'mb_stripos' => $this->mapNameSkipArg('stripos', 3),
            'mb_stristr' => $this->mapNameSkipArg('stristr', 3),
            'mb_strlen' => $this->mapNameSkipArg('strlen', 1),
            'mb_strpos' => $this->mapNameSkipArg('strpos', 3),
            'mb_strrchr' => $this->mapNameSkipArg('strrchr', 2),
            'mb_strripos' => $this->mapNameSkipArg('strripos', 3),
            'mb_strrpos' => $this->mapNameSkipArg('strrpos', 3),
            'mb_strstr' => $this->mapNameSkipArg('strstr', 3),
            'mb_strtolower' => $this->mapNameSkipArg('strtolower', 1),
            'mb_strtoupper' => $this->mapNameSkipArg('strtoupper', 1),
            'mb_substr_count' => $this->mapNameSkipArg('substr_count', 2),
            'mb_substr' => $this->mapNameSkipArg('substr', 3),
            'mb_convert_case' => $this->mapConvertCase(),
        ];

        $functionsToRemove = \array_filter($functionsMap, function ($isOn) {
            return !$isOn;
        });

        $this->converters = \array_diff_key($converters, $functionsToRemove);
    }

    private function mapName(string $functionName): callable
    {
        return function (Node\Expr\FuncCall $node) use ($functionName): Generator {
            yield $this->createNode($node, $functionName, $node->args);
        };
    }

    private function mapNameSkipArg(string $functionName, int $skipArgs): callable
    {
        return function (Node\Expr\FuncCall $node) use ($functionName, $skipArgs): Generator {
            yield $this->createNode($node, $functionName, \array_slice($node->args, 0, $skipArgs));
        };
    }

    private function mapConvertCase(): callable
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

            yield $this->createNode($node, $functionName, [$node->args[0]]);
        };
    }

    private function getConvertCaseModeValue(Node\Expr\FuncCall $node): ?int
    {
        if (\count($node->args) < 2) {
            return null;
        }

        $mode = $node->args[1]->value;

        if ($mode instanceof Node\Expr\ConstFetch) {
            $modeName = $mode->name->toString();

            if (\defined($modeName)) {
                return \constant($modeName);
            }

            if (isset(self::MB_CASES[$modeName])) {
                return self::MB_CASES[$modeName];
            }
        } elseif ($mode instanceof Node\Scalar\LNumber) {
            return $mode->value;
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
        $modes = \array_flip(self::MB_CASES);

        return isset($modes[$mode]) && \in_array($modes[$mode], $cases);
    }

    private function getFunctionName(Node $node): ?string
    {
        if (!$node instanceof Node\Expr\FuncCall || !$node->name instanceof Node\Name) {
            return null;
        }

        return \strtolower($node->name->toString());
    }

    private function createNode(Node\Expr\FuncCall $node, string $functionName, array $args): Node\Expr\FuncCall
    {
        return new Node\Expr\FuncCall(
            new Node\Name($functionName, $node->name->getAttributes()),
            $args,
            $node->getAttributes()
        );
    }
}
