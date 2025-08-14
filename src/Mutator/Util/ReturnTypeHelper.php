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

namespace Infection\Mutator\Util;

use Infection\PhpParser\Visitor\NextConnectingVisitor;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;

/**
 * @internal
 */
final class ReturnTypeHelper
{
    private const VOID = 'void';

    private const NULL = 'null';

    private readonly Identifier|Name|ComplexType|null $returnType;

    public function __construct(
        private readonly Node\Stmt\Return_ $node,
    ) {
        // We do not expect to see a return statement outside a function-like node.
        $this->returnType = ReflectionVisitor::getFunctionScope($this->node)->getReturnType();
    }

    public function hasVoidReturnType(): bool
    {
        if ($this->returnType === null) {
            return false;
        }

        if ($this->returnType instanceof ComplexType) {
            return false;
        }

        return $this->returnType->toLowerString() === self::VOID;
    }

    public function hasSpecificReturnType(): bool
    {
        if ($this->returnType === null) {
            return false;
        }

        // Complex types are specific return types
        if ($this->returnType instanceof ComplexType) {
            return true;
        }

        // Void is not considered a "real" return type for our purposes
        return $this->returnType->toLowerString() !== self::VOID;
    }

    public function isNullReturn(): bool
    {
        // Empty return (return;)
        if ($this->node->expr === null) {
            return true;
        }

        // Not a constant fetch, so it cannot be a return null
        if (!$this->node->expr instanceof Node\Expr\ConstFetch) {
            return false;
        }

        // Check for return null;
        return $this->node->expr->name->toLowerString() === self::NULL;
    }

    public function hasNextStmtNode(): bool
    {
        return $this->node->getAttribute(NextConnectingVisitor::NEXT_ATTRIBUTE) !== null;
    }
}
