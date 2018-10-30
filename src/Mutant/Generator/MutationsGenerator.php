<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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

namespace Infection\Mutant\Generator;

use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\MutableFileProcessed;
use Infection\Events\MutationGeneratingFinished;
use Infection\Events\MutationGeneratingStarted;
use Infection\Finder\SourceFilesFinder;
use Infection\Mutant\Exception\ParserException;
use Infection\Mutation;
use Infection\Mutator\Util\Mutator;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\Traverser\PriorityNodeTraverser;
use Infection\Visitor\FullyQualifiedClassNameVisitor;
use Infection\Visitor\MutationsCollectorVisitor;
use Infection\Visitor\ParentConnectorVisitor;
use Infection\Visitor\ReflectionVisitor;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Parser;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 */
final class MutationsGenerator
{
    /**
     * @var array source directories
     */
    private $srcDirs;

    /**
     * @var CodeCoverageData
     */
    private $codeCoverageData;

    /**
     * @var array
     */
    private $excludeDirsOrFiles;

    /**
     * @var Mutator[]
     */
    private $mutators;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Parser
     */
    private $parser;

    public function __construct(
        array $srcDirs,
        array $excludeDirsOrFiles,
        CodeCoverageData $codeCoverageData,
        array $mutators,
        EventDispatcherInterface $eventDispatcher,
        Parser $parser
    ) {
        $this->srcDirs = $srcDirs;
        $this->codeCoverageData = $codeCoverageData;
        $this->excludeDirsOrFiles = $excludeDirsOrFiles;
        $this->mutators = $mutators;
        $this->eventDispatcher = $eventDispatcher;
        $this->parser = $parser;
    }

    /**
     * @param bool $onlyCovered mutate only covered by tests lines of code
     * @param string $filter
     * @param NodeVisitorAbstract[] $extraNodeVisitors
     *
     * @return Mutation[]
     */
    public function generate(bool $onlyCovered, string $filter = '', array $extraNodeVisitors = []): array
    {
        $sourceFilesFinder = new SourceFilesFinder($this->srcDirs, $this->excludeDirsOrFiles);
        $files = $sourceFilesFinder->getSourceFiles($filter);
        $allFilesMutations = [[]];

        $this->eventDispatcher->dispatch(new MutationGeneratingStarted($files->count()));

        foreach ($files as $file) {
            if (!$onlyCovered || $this->hasTests($file)) {
                $allFilesMutations[] = $this->getMutationsFromFile($file, $onlyCovered, $extraNodeVisitors);
            }

            $this->eventDispatcher->dispatch(new MutableFileProcessed());
        }

        $this->eventDispatcher->dispatch(new MutationGeneratingFinished());

        return array_merge(...$allFilesMutations);
    }

    /**
     * @param SplFileInfo $file
     * @param bool $onlyCovered mutate only covered by tests lines of code
     * @param NodeVisitorAbstract[] $extraNodeVisitors extra visitors to influence to mutation collection process
     *
     * @return Mutation[]
     */
    private function getMutationsFromFile(SplFileInfo $file, bool $onlyCovered, array $extraNodeVisitors): array
    {
        try {
            /** @var Node[] $initialStatements */
            $initialStatements = $this->parser->parse($file->getContents());
        } catch (\Throwable $t) {
            throw ParserException::fromInvalidFile($file, $t);
        }

        $traverser = new PriorityNodeTraverser();
        $filePath = $file->getRealPath();
        \assert(\is_string($filePath));

        $mutationsCollectorVisitor = new MutationsCollectorVisitor(
            $this->mutators,
            $filePath,
            $initialStatements,
            $this->codeCoverageData,
            $onlyCovered
        );

        $traverser->addVisitor(new ParentConnectorVisitor(), 40);
        $traverser->addVisitor(new FullyQualifiedClassNameVisitor(), 30);
        $traverser->addVisitor(new ReflectionVisitor(), 20);
        $traverser->addVisitor($mutationsCollectorVisitor, 10);

        foreach ($extraNodeVisitors as $priority => $visitor) {
            $traverser->addVisitor($visitor, $priority);
        }

        $traverser->traverse($initialStatements);

        return $mutationsCollectorVisitor->getMutations();
    }

    private function hasTests(SplFileInfo $file): bool
    {
        $filePath = $file->getRealPath();
        \assert(\is_string($filePath));

        return $this->codeCoverageData->hasTests($filePath);
    }
}
