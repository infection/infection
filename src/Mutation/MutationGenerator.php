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

use function count;
use Infection\Event\EventDispatcher\EventDispatcher;
use Infection\Event\MutableFileWasProcessed;
use Infection\Event\MutationGenerationWasFinished;
use Infection\Event\MutationGenerationWasStarted;
use Infection\Mutator\Mutator;
use Infection\TestFramework\Coverage\LineCodeCoverage;
use PhpParser\NodeVisitor;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class MutationGenerator
{
    /**
     * @var SplFileInfo[]
     */
    private $sourceFiles;

    /**
     * @var Mutator[]
     */
    private $mutators;

    private $codeCoverageData;
    private $eventDispatcher;
    private $fileMutationGenerator;

    /**
     * @param SplFileInfo[] $sourceFiles
     * @param Mutator[] $mutators
     */
    public function __construct(
        array $sourceFiles,
        LineCodeCoverage $codeCoverageData,
        array $mutators,
        EventDispatcher $eventDispatcher,
        FileMutationGenerator $fileMutationGenerator
    ) {
        Assert::allIsInstanceOf($sourceFiles, SplFileInfo::class);
        Assert::allIsInstanceOf($mutators, Mutator::class);

        $this->sourceFiles = $sourceFiles;
        $this->codeCoverageData = $codeCoverageData;
        $this->mutators = $mutators;
        $this->eventDispatcher = $eventDispatcher;
        $this->fileMutationGenerator = $fileMutationGenerator;
    }

    /**
     * @param bool $onlyCovered Mutates only covered by tests lines of code
     * @param NodeVisitor[] $extraNodeVisitors
     *
     * @throws UnparsableFile
     *
     * @return Mutation[]
     */
    public function generate(bool $onlyCovered, array $extraNodeVisitors = []): array
    {
        $allFilesMutations = [[]];

        $this->eventDispatcher->dispatch(new MutationGenerationWasStarted(count($this->sourceFiles)));

        foreach ($this->sourceFiles as $fileInfo) {
            $allFilesMutations[] = $this->fileMutationGenerator->generate(
                $fileInfo,
                $onlyCovered,
                $this->codeCoverageData,
                $this->mutators,
                $extraNodeVisitors
            );

            $this->eventDispatcher->dispatch(new MutableFileWasProcessed());
        }

        $this->eventDispatcher->dispatch(new MutationGenerationWasFinished());

        return array_merge(...$allFilesMutations);
    }
}
