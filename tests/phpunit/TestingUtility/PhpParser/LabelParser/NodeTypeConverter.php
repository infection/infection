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

namespace Infection\Tests\TestingUtility\PhpParser\LabelParser;

use RuntimeException;
use function class_exists;
use function rtrim;
use function sprintf;
use function str_ends_with;
use function str_replace;

/**
 * Converts NodeDumper format node types to fully qualified class names.
 *
 * Examples:
 *   - Expr_Variable -> PhpParser\Node\Expr\Variable
 *   - Expr_Cast_Int -> PhpParser\Node\Expr\Cast\Int_
 *   - Stmt_Function -> PhpParser\Node\Stmt\Function_
 *   - Scalar_String_ -> PhpParser\Node\Scalar\String_
 */
final class NodeTypeConverter
{
    /**
     * @param string $nodeType Node type in NodeDumper format (e.g., "Expr_Variable")
     * @param string $label Label name for error messages
     * @param int $line Line number for error messages
     *
     * @return class-string Fully qualified class name
     */
    public static function convertToFqn(string $nodeType, string $label, int $line): string
    {
        // Check if the input ends with an underscore
        // If so, that underscore is part of the class name, not a separator
        $hasTrailingUnderscore = str_ends_with($nodeType, '_');

        // If it has a trailing underscore, remove it for processing
        if ($hasTrailingUnderscore) {
            $nodeType = rtrim($nodeType, '_');
        }

        // Convert underscore-separated format to namespace format
        // Example: Expr_Variable -> Expr\Variable
        $namespacedType = str_replace('_', '\\', $nodeType);

        // Add back the trailing underscore if it was present in the input
        if ($hasTrailingUnderscore) {
            $namespacedType .= '_';
        }

        // Prepend PhpParser\Node namespace
        $fqn = 'PhpParser\\Node\\' . $namespacedType;

        // Try the converted FQN
        if (class_exists($fqn)) {
            return $fqn;
        }

        // Some node classes have trailing underscores for PHP reserved keywords
        // even if the input doesn't specify it (e.g., Stmt_Function -> Function_)
        // Try adding a trailing underscore if we haven't already
        if (!$hasTrailingUnderscore) {
            $fqnWithUnderscore = $fqn . '_';

            if (class_exists($fqnWithUnderscore)) {
                return $fqnWithUnderscore;
            }
        }

        // Neither worked, throw exception
        throw new RuntimeException(
            sprintf(
                'Invalid node type "%s" for label "%s" at line %d. Expected a valid PhpParser node type (e.g., Expr_Variable, Stmt_Function).',
                $nodeType,
                $label,
                $line,
            ),
        );
    }
}

