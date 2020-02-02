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

use Infection\Mutator\Mutator;
use Infection\Mutator\NodeMutationGenerator;
use Infection\TestFramework\Coverage\LineCodeCoverage;
use Infection\Visitor\IgnoreNode\NodeIgnorer;
use Infection\Visitor\MutationsCollectorVisitor;
use Symfony\Component\Finder\SplFileInfo;
use Webmozart\Assert\Assert;

/**
 * @internal
 * @final
 */
class FileMutationGenerator
{
    private $parser;
    private $traverserFactory;

    public function __construct(
        FileParser $parser,
        NodeTraverserFactory $traverserFactory
    ) {
        $this->parser = $parser;
        $this->traverserFactory = $traverserFactory;
    }

    /**
     * @param Mutator[] $mutators
     * @param NodeIgnorer[] $nodeIgnorers
     *
     * @throws UnparsableFile
     *
     * @return Mutation[]
     */
    public function generate(
        SplFileInfo $fileInfo,
        bool $onlyCovered,
        LineCodeCoverage $codeCoverage,
        array $mutators,
        array $nodeIgnorers
    ): array {
        Assert::allIsInstanceOf($mutators, Mutator::class);
        Assert::allIsInstanceOf($nodeIgnorers, NodeIgnorer::class);

        $filePath = $fileInfo->getRealPath() === false
            ? $fileInfo->getPathname()
            : $fileInfo->getRealPath()
        ;

        if ($onlyCovered && !$codeCoverage->hasTests($filePath)) {
            return [];
        }

        $initialStatements = $this->parser->parse($fileInfo);

        $mutationsCollectorVisitor = new MutationsCollectorVisitor(
            new NodeMutationGenerator(
                $mutators,
                $filePath,
                $initialStatements,
                $codeCoverage,
                $onlyCovered
            )
        );

        $traverser = $this->traverserFactory->create($mutationsCollectorVisitor, $nodeIgnorers);

        $traverser->traverse($initialStatements);

        return $mutationsCollectorVisitor->getMutations();
    }
}
