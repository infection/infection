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
use Infection\Event\MutableFileWasProcessed;
use Infection\Event\MutationGenerationWasFinished;
use Infection\Event\MutationGenerationWasStarted;
use Infection\IterableCounter;
use Infection\Mutator\Mutator;
use Infection\PhpParser\UnparsableFile;
use Infection\PhpParser\Visitor\IgnoreNode\NodeIgnorer;
use Infection\TestFramework\Coverage\SourceFileData;
use Infection\TestFramework\Coverage\SourceFileDataFactory;
use Infection\TestFramework\Coverage\SourceFileDataProvider;
use Infection\TestFramework\Coverage\XmlReport\FileCodeCoverageProvider;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class MutationGenerator
{
    /**
     * @var SourceFileDataProvider|SourceFileDataFactory
     */
    private $sourceFileDataProvider;
    private $coverageProvider;

    /**
     * @var Mutator[]
     */
    private $mutators;

    private $eventDispatcher;
    private $fileMutationGenerator;
    private $runConcurrently;

    /**
     * @param Mutator[] $mutators
     */
    public function __construct(
        SourceFileDataProvider $sourceFileDataProvider,
        FileCodeCoverageProvider $coverageProvider,
        array $mutators,
        EventDispatcher $eventDispatcher,
        FileMutationGenerator $fileMutationGenerator,
        bool $runConcurrently
    ) {
        Assert::allIsInstanceOf($mutators, Mutator::class);

        $this->sourceFileDataProvider = $sourceFileDataProvider;
        $this->coverageProvider = $coverageProvider;
        $this->mutators = $mutators;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileMutationGenerator = $fileMutationGenerator;
        $this->runConcurrently = $runConcurrently;
    }

    /**
     * @param bool $onlyCovered Mutates only covered by tests lines of code
     * @param NodeIgnorer[] $nodeIgnorers
     *
     * @throws UnparsableFile
     *
     * @return iterable<Mutation>
     */
    public function generate(bool $onlyCovered, array $nodeIgnorers): iterable
    {
        $files = $this->sourceFileDataProvider->provideFiles();

        $numberOfFiles = IterableCounter::bufferAndCountIfNeeded($files, $this->runConcurrently);

        $this->eventDispatcher->dispatch(new MutationGenerationWasStarted($numberOfFiles));

        /** @var SourceFileData $fileData */
        foreach ($files as $fileData) {
            yield from $this->fileMutationGenerator->generate(
                $fileData,
                $onlyCovered,
                $this->coverageProvider->provideFor($fileData),
                $this->mutators,
                $nodeIgnorers
            );

            $this->eventDispatcher->dispatch(new MutableFileWasProcessed());
        }

        $this->eventDispatcher->dispatch(new MutationGenerationWasFinished());
    }
}
