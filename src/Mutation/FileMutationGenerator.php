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

use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\MutationGenerationForSourceFileWasFinished;
use Infection\Event\MutationGenerationForSourceFileWasStarted;
use Infection\FileSystem\FileStore;
use Infection\Mutator\Mutator;
use Infection\Mutator\NodeMutationGenerator;
use Infection\PhpParser\FileParser;
use Infection\PhpParser\NodeTraverserFactory;
use Infection\PhpParser\UnparsableFile;
use Infection\PhpParser\Visitor\MutationCollectorVisitor;
use Infection\Source\Matcher\SourceLineMatcher;
use Infection\TestFramework\Tracing\Throwable\NoTraceFound;
use Infection\TestFramework\Tracing\Trace\EmptyTrace;
use Infection\TestFramework\Tracing\Trace\LineRangeCalculator;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\TestFramework\Tracing\Tracer;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Token;
use SplFileInfo;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class FileMutationGenerator
{
    public function __construct(
        private readonly FileParser $parser,
        private readonly NodeTraverserFactory $traverserFactory,
        private readonly LineRangeCalculator $lineRangeCalculator,
        private readonly SourceLineMatcher $sourceLineMatcher,
        private readonly Tracer $tracer,
        private readonly FileStore $fileStore,
        private readonly EventDispatcher $eventDispatcher,
    ) {
    }

    /**
     * @param Mutator<Node>[] $mutators
     *
     * @throws UnparsableFile
     *
     * @return iterable<Mutation>
     */
    public function generate(
        SplFileInfo $sourceFile,
        bool $onlyCovered,
        array $mutators,
    ): iterable {
        Assert::allIsInstanceOf($mutators, Mutator::class);

        $trace = $this->trace($sourceFile);

        if ($onlyCovered && !$trace->hasTests()) {
            return;
        }

        [$initialStatements, $originalFileTokens] = $this->createAst($sourceFile);

        yield from $this->generateMutations(
            $mutators,
            $sourceFile,
            $initialStatements,
            $trace,
            $onlyCovered,
            $originalFileTokens,
        );
    }

    /**
     * @param Mutator<Node>[] $mutators
     * @param Stmt[] $initialStatements
     * @param Token[] $originalFileTokens
     *
     * @return iterable<Mutation>
     */
    private function generateMutations(array $mutators,
        SplFileInfo $sourceFile,
        mixed $initialStatements,
        Trace $trace,
        bool $onlyCovered,
        mixed $originalFileTokens,
    ): iterable {
        $mutationCollectorVisitor = new MutationCollectorVisitor(
            new NodeMutationGenerator(
                mutators: $mutators,
                filePath: $sourceFile->getRealPath(),
                fileNodes: $initialStatements,
                trace: $trace,
                onlyCovered: $onlyCovered,
                lineRangeCalculator: $this->lineRangeCalculator,
                sourceLineMatcher: $this->sourceLineMatcher,
                originalFileTokens: $originalFileTokens,
                originalFileContent: $this->fileStore->getContents($sourceFile),
            ),
        );

        $this->eventDispatcher->dispatch(
            new MutationGenerationForSourceFileWasStarted(),
        );

        $traverser = $this->traverserFactory->create($mutationCollectorVisitor);
        $traverser->traverse($initialStatements);

        $sourceFileMutationIds = [];

        foreach ($mutationCollectorVisitor->getMutations() as $mutation) {
            $sourceFileMutationIds[] = $mutation->getHash();

            yield $mutation;
        }

        $this->eventDispatcher->dispatch(
            new MutationGenerationForSourceFileWasFinished(
                $sourceFileMutationIds,
            ),
        );
    }

    /**
     * @throws UnparsableFile
     *
     * @return array{Stmt[], Token[]}
     */
    private function createAst(SplFileInfo $sourceFile): array
    {
        [$initialStatements, $originalFileTokens] = $this->parser->parse($sourceFile);

        // Pre-traverse the nodes to connect them
        $preTraverser = $this->traverserFactory->createPreTraverser();
        $preTraverser->traverse($initialStatements);

        return [$initialStatements, $originalFileTokens];
    }

    /**
     * @throws UnparsableFile
     *
     * @return array{Stmt[], Token[]}
     */
    private function createAst(SplFileInfo $sourceFile): array
    {
        [$initialStatements, $originalFileTokens] = $this->parser->parse($sourceFile);

        // Pre-traverse the nodes to connect them
        $preTraverser = $this->traverserFactory->createPreTraverser();
        $preTraverser->traverse($initialStatements);

        return [$initialStatements, $originalFileTokens];
    }

    private function trace(SplFileInfo $sourceFile): Trace
    {
        try {
            return $this->tracer->trace($sourceFile);
        } catch (NoTraceFound) {
            return new EmptyTrace($sourceFile);
        }
    }
}
