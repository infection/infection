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

use Infection\Ast\Metadata\TraverseContext;
use Infection\Ast\NodeVisitor\AddTestsVisitor;
use Infection\Ast\NodeVisitor\ExcludeNonSupportedNodesVisitor;
use Infection\Ast\NodeVisitor\ExcludeUnchangedNodesVisitor;
use Infection\Ast\NodeVisitor\ExcludeUncoveredNodesVisitor;
use Infection\Ast\NodeVisitor\NameResolverFactory;
use Infection\PhpParser\Visitor\IgnoreAllMutationsAnnotationReaderVisitor;
use Infection\PhpParser\Visitor\IgnoreNode\AbstractMethodIgnorer;
use Infection\PhpParser\Visitor\IgnoreNode\ChangingIgnorer;
use Infection\PhpParser\Visitor\IgnoreNode\InterfaceIgnorer;
use Infection\PhpParser\Visitor\IgnoreNode\NodeIgnorer;
use Infection\PhpParser\Visitor\NextConnectingVisitor;
use Infection\PhpParser\Visitor\NonMutableNodesIgnorerVisitor;
use Infection\PhpParser\Visitor\ReflectionVisitor;
use Infection\Source\Matcher\SourceLineMatcher;
use Infection\TestFramework\Tracing\Trace\LineRangeCalculator;
use Infection\TestFramework\Tracing\Trace\Trace;
use PhpParser\NodeTraverser;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitor\ParentConnectingVisitor;
use PhpParser\Token;
use SplObjectStorage;

/**
 * @internal
 */
final readonly class NodeTraverserFactory
{
    public function __construct(
        private SourceLineMatcher $sourceLineMatcher,
        private LineRangeCalculator $lineRangeCalculator,
        private bool $onlyCovered,
    ) {
    }

    /**
     * @deprecated
     * @param NodeIgnorer[] $nodeIgnorers
     */
    public function legacyCreate(NodeVisitor $mutationVisitor, array $nodeIgnorers): NodeTraverserInterface
    {
        $changingIgnorer = new ChangingIgnorer();
        $nodeIgnorers[] = $changingIgnorer;

        $nodeIgnorers[] = new InterfaceIgnorer();
        $nodeIgnorers[] = new AbstractMethodIgnorer();

        $traverser = new NodeTraverser(new CloningVisitor());

        $traverser->addVisitor(new IgnoreAllMutationsAnnotationReaderVisitor($changingIgnorer, new SplObjectStorage()));
        $traverser->addVisitor(new NonMutableNodesIgnorerVisitor($nodeIgnorers));
        $traverser->addVisitor(new NameResolver(
            null,
            [
                'preserveOriginalNames' => true,
                // must be `false` for pretty-printing to work properly
                // @see https://github.com/nikic/PHP-Parser/blob/master/doc/component/Pretty_printing.markdown#formatting-preserving-pretty-printing
                'replaceNodes' => false,
            ]),
        );
        $traverser->addVisitor(new ParentConnectingVisitor());
        $traverser->addVisitor(new ReflectionVisitor());
        $traverser->addVisitor($mutationVisitor);

        return $traverser;
    }

    /**
     * TODO: this replaces the "createPreTraverser": this "pre" traverse, which
     *   is the first one, is where we enrich all the AST.
     *
     * @param Token[] $originalFileTokens
     */
    public function createFirstTraverser(
        Trace $trace,
        array $originalFileTokens,
    ): NodeTraverserInterface {
        $context = new TraverseContext(
            $trace->getRealPath(),
            $trace,
        );

        $traverser = new NodeTraverser(
            NameResolverFactory::create(),
            new ParentConnectingVisitor(),
            new NextConnectingVisitor(),
            new ReflectionVisitor(),

            // We need to place if after other annotated elements.
            // It would be nicer to have it before, to be able to skip non-relevant nodes,
            // but currently, it skips anything not touched, e.g. even the class name although
            // a node of a method of the class is touched.
            // TODO: review this implementation
            new ExcludeUnchangedNodesVisitor(
                $context,
                $this->sourceLineMatcher,
            ),
            new ExcludeNonSupportedNodesVisitor(),
            new AddTestsVisitor(
                $context,
                $this->lineRangeCalculator,
            ),
        );

        if ($this->onlyCovered) {
            $traverser->addVisitor(new ExcludeUncoveredNodesVisitor());
        }

        return $traverser;
    }

    /**
     * TODO: replaces the `::create()`.
     */
    public function createSecondTraverser(NodeVisitor $mutationVisitor): NodeTraverserInterface
    {
        return new NodeTraverser(
            new CloningVisitor(),
            $mutationVisitor,
        );
    }

    /**
     * @deprecated
     */
    public function createPreTraverser(): NodeTraverserInterface
    {
        return new NodeTraverser(
            new NextConnectingVisitor(),
        );
    }
}
