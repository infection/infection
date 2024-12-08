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

namespace Infection\Mutation;

use Infection\Differ\FilesDiffChangedLines;
use Infection\Mutator\Mutator;
use Infection\Mutator\NodeMutationGenerator;
use Infection\PhpParser\FileParser;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\PhpParser\UnparsableFile;
use Infection\PhpParser\Visitor\IgnoreNode\NodeIgnorer;
use Infection\PhpParser\Visitor\MutationCollectorVisitor;
use Infection\TestFramework\Coverage\LineRangeCalculator;
use Infection\TestFramework\Coverage\Trace;
use PhpParser\Node;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class FileMutationGenerator
{
    public function __construct(private readonly FileParser $parser, private readonly NodeTraverserFactory $traverserFactory, private readonly LineRangeCalculator $lineRangeCalculator, private readonly FilesDiffChangedLines $filesDiffChangedLines, private readonly bool $isForGitDiffLines, private readonly ?string $gitDiffBase)
    {
    }

    /**
     * @param Mutator<Node>[] $mutators
     * @param NodeIgnorer[] $nodeIgnorers
     *
     * @throws UnparsableFile
     *
     * @return iterable<Mutation>
     */
    public function generate(
        Trace $trace,
        bool $onlyCovered,
        array $mutators,
        array $nodeIgnorers,
    ): iterable {
        Assert::allIsInstanceOf($mutators, Mutator::class);
        Assert::allIsInstanceOf($nodeIgnorers, NodeIgnorer::class);

        if ($onlyCovered && !$trace->hasTests()) {
            return;
        }

        $initialStatements = $this->parser->parse($trace->getSourceFileInfo());

        $mutationCollectorVisitor = new MutationCollectorVisitor(
            new NodeMutationGenerator(
                $mutators,
                $trace->getRealPath(),
                $initialStatements,
                $trace,
                $onlyCovered,
                $this->isForGitDiffLines,
                $this->gitDiffBase,
                $this->lineRangeCalculator,
                $this->filesDiffChangedLines,
            ),
        );

        $traverser = $this->traverserFactory->create($mutationCollectorVisitor, $nodeIgnorers);

        $traverser->traverse($initialStatements);

        yield from $mutationCollectorVisitor->getMutations();
    }
}
