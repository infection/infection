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

namespace newSrc\AST\NodeVisitor;

use newSrc\AST\Metadata\Annotation;
use newSrc\AST\Metadata\NodeAnnotator;
use newSrc\AST\Metadata\SymbolAnnotator;
use newSrc\TestFramework\Trace\Symbol\Symbol;
use newSrc\TestFramework\Tracing\Tracer;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

/**
 * A user can select a piece of source code to mutation. For example "*Str::trim*()". When
 * such a selection is provided, this visitor will exclude any node that do not belong to
 * this selection.
 *
 * Note that this strategy is incompatible with LabelNodesAsEligibleVisitor.
 *
 * @see LabelNodesAsEligibleVisitor
 */
final class ApplyUserSelectionVisitor extends NodeVisitorAbstract
{
    // TODO
    // Will make use of the SymbolAnnotator.
    // A few examples of how it should work:
    //
    // Case: "Acme\Str::trimLineReturns()" -> MethodReference Symbol
    //      - we enter a namespace Foo -> stop the the traversal of the current node & children
    //      - we enter the namespace "Acme": continue
    //      - we enter a class A -> stop the the traversal of the current node & children
    //      - we enter a class Str: continue
    //      - we enter a method "::bar()": stop the the traversal of the current node & children
    //      - we enter a method "::trimLineReturns()": start to mark nodes as eligible
    //      - we leave the method "::trimLineReturns()": stop the the traversal of the current node & children?
    //
    // Case: to specify how it works with patterns
    // Note that maybe we should allow multiple selections in which case there is a bit more to figure out/specify.
}
