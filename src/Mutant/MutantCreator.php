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

namespace Infection\Mutant;

use Infection\Differ\Differ;
use Infection\MutationInterface;
use Infection\TestFramework\Coverage\CodeCoverageData;
use Infection\Visitor\CloneVisitor;
use Infection\Visitor\MutatorVisitor;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use function Safe\file_get_contents;

/**
 * @internal
 */
final class MutantCreator
{
    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var Differ
     */
    private $differ;

    /**
     * @var Standard
     */
    private $prettyPrinter;

    /**
     * @var string[]
     */
    private $prettyPrintedCache = [];

    public function __construct(string $tempDir, Differ $differ, Standard $prettyPrinter)
    {
        $this->tempDir = $tempDir;
        $this->differ = $differ;
        $this->prettyPrinter = $prettyPrinter;
    }

    public function create(MutationInterface $mutation, CodeCoverageData $codeCoverageData): MutantInterface
    {
        $mutatedFilePath = sprintf('%s/mutant.%s.infection.php', $this->tempDir, $mutation->getHash());

        $mutatedCode = $this->createMutatedCode($mutation, $mutatedFilePath);

        $originalPrettyPrintedFile = $this->getOriginalPrettyPrintedFile($mutation->getOriginalFilePath(), $mutation->getOriginalFileAst());

        $diff = $this->differ->diff($originalPrettyPrintedFile, $mutatedCode);

        return new Mutant(
            $mutatedFilePath,
            $mutation,
            $diff,
            $mutation->isCoveredByTest(),
            $codeCoverageData->getAllTestsFor($mutation)
        );
    }

    private function createMutatedCode(MutationInterface $mutation, string $mutatedFilePath): string
    {
        if (is_readable($mutatedFilePath)) {
            $mutatedCode = file_get_contents($mutatedFilePath);

            return $mutatedCode;
        }

        $traverser = new NodeTraverser();

        $traverser->addVisitor(new CloneVisitor());
        $traverser->addVisitor(new MutatorVisitor($mutation));

        $mutatedStatements = $traverser->traverse($mutation->getOriginalFileAst());

        $mutatedCode = $this->prettyPrinter->prettyPrintFile($mutatedStatements);
        file_put_contents($mutatedFilePath, $mutatedCode);

        return $mutatedCode;
    }

    private function getOriginalPrettyPrintedFile(string $originalFilePath, array $originalStatements): string
    {
        return $this->prettyPrintedCache[$originalFilePath]
            ?? $this->prettyPrintedCache[$originalFilePath] = $this->prettyPrinter->prettyPrintFile($originalStatements);
    }
}
