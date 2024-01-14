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

namespace Infection\PhpParser\PrettyPrinter;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;
use PhpParser\PrettyPrinter\Standard;
use function preg_replace;

/**
 * Keep compatibility between PHP-Parser 4 and 5 as the 5th version follow PSR-12.
 * Could be removed when PHP-Parser 4 support is dropped.
 *
 * @see https://github.com/nikic/PHP-Parser/blob/master/UPGRADE-5.0.md#changes-to-the-pretty-printer
 */
final class BackwardCompatibleStandard extends Standard
{
    private const REPLACE_COLON_WITH_SPACE_REGEX = '#(^.*function .*\\(.*\\)) : #';

    public function __construct(array $options = [])
    {
        $options['shortArraySyntax'] = true;

        parent::__construct($options);
    }

    protected function pStmt_ClassMethod(Stmt\ClassMethod $node): string
    {
        $content = parent::pStmt_ClassMethod($node);

        if (!$node->returnType instanceof Node) {
            return $content;
        }

        return preg_replace(self::REPLACE_COLON_WITH_SPACE_REGEX, '$1: ', $content);
    }

    protected function pStmt_Function(Stmt\Function_ $node): string
    {
        $content = parent::pStmt_Function($node);

        if (!$node->returnType instanceof Node) {
            return $content;
        }

        return preg_replace(self::REPLACE_COLON_WITH_SPACE_REGEX, '$1: ', $content);
    }

    protected function pExpr_ClosureUse(Expr\ClosureUse $node): string
    {
        $content = parent::pExpr_ClosureUse($node);

        return preg_replace(self::REPLACE_COLON_WITH_SPACE_REGEX, '$1: ', $content);
    }
}
