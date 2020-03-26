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

namespace Infection\PhpParser\Visitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class SideEffectPredictorVisitor extends NodeVisitorAbstract
{
    public const HAS_NODES_WITH_SIDE_EFFECTS_KEY = 'withSideEffects';

    private const INITIAL_DEPTH = 0;

    /**
     * @var bool[]
     */
    private $seenMethodCall = [];

    /**
     * @var bool[]
     */
    private $seenNonMethodCall = [];

    /**
     * @var int
     */
    private $depth = self::INITIAL_DEPTH;

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof Node\Stmt\Expression) {
            ++$this->depth;
            $this->seenMethodCall[$this->depth] = false;
            $this->seenNonMethodCall[$this->depth] = false;

            return null;
        }

        if ($this->depth === self::INITIAL_DEPTH) {
            // Not inside a statement.
            return null;
        }

        if ($this->seenMethodCall[$this->depth] === false) {
            $this->seenMethodCall[$this->depth] = $node instanceof Node\Expr\MethodCall;
        }

        if ($this->seenNonMethodCall[$this->depth] === false) {
            $this->seenNonMethodCall[$this->depth] = $node instanceof Node\Expr\FuncCall
                || $node instanceof Node\Expr\StaticCall
                || $node instanceof Node\Expr\New_
            ;
        }

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        if ($node instanceof Node\Stmt\Expression) {
            --$this->depth;

            $node->setAttribute(
                self::HAS_NODES_WITH_SIDE_EFFECTS_KEY,
                array_pop($this->seenMethodCall) && !array_pop($this->seenNonMethodCall)
            );
        }

        return null;
    }
}
