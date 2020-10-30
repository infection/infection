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
use Infection\Mutation\Mutation;
use PhpParser\Node;
use PhpParser\PrettyPrinterAbstract;
use function Safe\sprintf;

/**
 * @internal
 * @final
 */
class MutantFactory
{
    private string $tmpDir;
    private Differ $differ;
    private PrettyPrinterAbstract $printer;

    /**
     * @var string[]
     */
    private array $printedFileCache = [];
    private MutantCodeFactory $mutantCodeFactory;

    public function __construct(
        string $tmpDir,
        Differ $differ,
        PrettyPrinterAbstract $printer,
        MutantCodeFactory $mutantCodeFactory
    ) {
        $this->tmpDir = $tmpDir;
        $this->differ = $differ;
        $this->printer = $printer;
        $this->mutantCodeFactory = $mutantCodeFactory;
    }

    public function create(Mutation $mutation): Mutant
    {
        $mutantFilePath = sprintf(
            '%s/mutant.%s.infection.php',
            $this->tmpDir,
            $mutation->getHash()
        );

        $mutatedCode = $this->mutantCodeFactory->createCode($mutation);

        return new Mutant(
            $mutantFilePath,
            $mutation,
            $mutatedCode,
            $this->createMutantDiff($mutation, $mutatedCode),
            $this->getOriginalPrettyPrintedFile($mutation->getOriginalFilePath(), $mutation->getOriginalFileAst())
        );
    }

    private function createMutantDiff(Mutation $mutation, string $mutantCode): string
    {
        $originalPrettyPrintedFile = $this->getOriginalPrettyPrintedFile(
            $mutation->getOriginalFilePath(),
            $mutation->getOriginalFileAst()
        );

        return $this->differ->diff($originalPrettyPrintedFile, $mutantCode);
    }

    /**
     * @param Node[] $originalStatements
     */
    private function getOriginalPrettyPrintedFile(string $originalFilePath, array $originalStatements): string
    {
        // The same file may be mutated multiple times hence we can memoize that call
        return $this->printedFileCache[$originalFilePath]
            ?? $this->printedFileCache[$originalFilePath] = $this->printer->prettyPrintFile($originalStatements);
    }
}
