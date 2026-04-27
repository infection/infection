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

namespace Infection\PhpParser;

use Infection\PhpParser\Visitor\AddTestsVisitor;
use Infection\PhpParser\Visitor\ExcludeIgnoredNodesVisitor;
use Infection\PhpParser\Visitor\ExcludeNonMutableCodeVisitor;
use Infection\PhpParser\Visitor\ExcludeUnchangedLinesVisitor;
use Infection\PhpParser\Visitor\ExcludeUntestedNodesVisitor;
use Infection\PhpParser\Visitor\IgnoreNode\AbstractMethodIgnorer;
use Infection\PhpParser\Visitor\IgnoreNode\InterfaceIgnorer;
use Infection\PhpParser\Visitor\LabelNodesAsEligibleVisitor;
use Infection\PhpParser\Visitor\NameResolverFactory;
use Infection\PhpParser\Visitor\NextConnectingVisitor;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use Infection\PhpParser\Visitor\SkipIgnoredNodesVisitor;
use Infection\Source\Matcher\SourceLineMatcher;
use Infection\TestFramework\Tracing\Trace\LineRangeCalculator;
use Infection\TestFramework\Tracing\Trace\Trace;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use SplFileInfo;

/**
 * @internal
 * @final
 */
readonly class NodeTraverserFactory
{
    public function __construct(
        private SourceLineMatcher $sourceLineMatcher,
        private LineRangeCalculator $lineRangeCalculator,
        private bool $onlyCovered,
    ) {
    }

    /**
     * @see /doc/nomenclature.md#ast-enrichment
     */
    public function createEnrichmentTraverser(
        SplFileInfo $sourceFile,
        Trace $trace,
    ): NodeTraverserInterface {
        $nodeIgnorers = [
            new InterfaceIgnorer(),
            new AbstractMethodIgnorer(),
        ];

        $visitors = [
            new NextConnectingVisitor(),
            new LabelNodesAsEligibleVisitor(),
            new ExcludeIgnoredNodesVisitor(),
            new SkipIgnoredNodesVisitor($nodeIgnorers),
            NameResolverFactory::create(),
            new ParentConnectingVisitor(),
            new ReflectionVisitor(),
            new ExcludeNonMutableCodeVisitor(),
            new ExcludeUnchangedLinesVisitor(
                $this->sourceLineMatcher,
                $sourceFile->getRealPath(),
            ),
            new AddTestsVisitor(
                $trace,
                $this->lineRangeCalculator,
            ),
        ];

        if ($this->onlyCovered) {
            $visitors[] = new ExcludeUntestedNodesVisitor();
        }

        return new NodeTraverser(...$visitors);
    }

    public function createMutationTraverser(NodeVisitor $mutationVisitor): NodeTraverserInterface
    {
        return new NodeTraverser(
            new NodeVisitor\CloningVisitor(),
            $mutationVisitor,
        );
    }
}
