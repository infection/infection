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

namespace Infection\Tests\Architecture\PHPat\Selector\Support\Analyser;

use function array_keys;
use function explode;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;
use function str_contains;
use function strtolower;

final class EnvironmentVariableUsageVisitor extends NodeVisitorAbstract
{
    /**
     * @var array<string, true>
     */
    private array $environmentVariables = [];

    public function enterNode(Node $node): null
    {
        $environmentVariable = match (true) {
            $node instanceof FuncCall => self::findPutenvEnvironmentVariable($node),
            $node instanceof ArrayDimFetch => self::findEnvEnvironmentVariable($node),
            default => null,
        };

        if ($environmentVariable !== null && $environmentVariable !== '') {
            $this->environmentVariables[$environmentVariable] = true;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function getEnvironmentVariables(): array
    {
        return array_keys($this->environmentVariables);
    }

    private static function findPutenvEnvironmentVariable(FuncCall $functionCall): ?string
    {
        if (
            !$functionCall->name instanceof Name
            || strtolower($functionCall->name->getLast()) !== 'putenv'
        ) {
            return null;
        }

        $argument = $functionCall->getArgs()[0]->value ?? null;
        $prefix = self::findStringPrefix($argument);

        if ($prefix === null) {
            return null;
        }

        if (str_contains($prefix, '=')) {
            return explode('=', $prefix, 2)[0];
        }

        return $argument instanceof String_ ? $prefix : null;
    }

    private static function findEnvEnvironmentVariable(ArrayDimFetch $arrayDimFetch): ?string
    {
        return $arrayDimFetch->var instanceof Variable
            && $arrayDimFetch->var->name === '_ENV'
            && $arrayDimFetch->dim instanceof String_
                ? $arrayDimFetch->dim->value
                : null;
    }

    private static function findStringPrefix(?Node $node): ?string
    {
        if ($node instanceof String_) {
            return $node->value;
        }

        if ($node instanceof Concat) {
            return self::findStringPrefix($node->left);
        }

        return null;
    }
}
