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
use Later\Interfaces\Deferred;
use function Later\lazy;
use PhpParser\Node;
use PhpParser\PrettyPrinterAbstract;
use function sprintf;

/**
 * @internal
 * @final
 */
class MutantFactory
{
    /**
     * @var string[]
     */
    private array $printedFileCache = [];

    public function __construct(
        private readonly string $tmpDir,
        private readonly Differ $differ,
        private readonly PrettyPrinterAbstract $printer,
        private readonly MutantCodeFactory $mutantCodeFactory,
    ) {
    }

    public function create(Mutation $mutation): Mutant
    {
        $mutantFilePath = sprintf(
            '%s/mutant.%s.infection.php',
            $this->tmpDir,
            $mutation->getHash(),
        );

        $mutatedCode = lazy($this->createMutatedCode($mutation));
        $originalPrettyPrintedFile = lazy($this->getOriginalPrettyPrintedFile($mutation->getOriginalFilePath(), $mutation->getOriginalFileAst()));

        return new Mutant(
            $mutantFilePath,
            $mutation,
            $mutatedCode,
            lazy($this->createMutantDiff($originalPrettyPrintedFile, $mutatedCode)),
            $originalPrettyPrintedFile,
        );
    }

    /** @return iterable<string> */
    private function createMutatedCode(Mutation $mutation): iterable
    {
        yield $this->mutantCodeFactory->createCode($mutation);
    }

    /**
     * @param Deferred<string> $originalPrettyPrintedFile
     * @param Deferred<string> $mutantCode
     *
     * @return iterable<string>
     */
    private function createMutantDiff(Deferred $originalPrettyPrintedFile, Deferred $mutantCode): iterable
    {
        yield $this->differ->diff($originalPrettyPrintedFile->get(), $mutantCode->get());
    }

    /**
     * @param Node[] $originalStatements
     *
     * @return iterable<string>
     */
    private function getOriginalPrettyPrintedFile(string $originalFilePath, array $originalStatements): iterable
    {
        // The same file may be mutated multiple times hence we can memoize that call
        yield $this->printedFileCache[$originalFilePath] ??= $this->printer->prettyPrintFile($originalStatements);
    }
}
