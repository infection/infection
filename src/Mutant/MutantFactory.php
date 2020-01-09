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
use Infection\Mutation;
use function is_readable;
use PhpParser\PrettyPrinterAbstract;
use function Safe\file_get_contents;
use function Safe\file_put_contents;

/**
 * @internal
 */
final class MutantFactory
{
    private $tmpDir;
    private $differ;
    private $prettyPrinter;

    /**
     * @var string[]
     */
    private $prettyPrintedCache = [];
    private $mutantCodeFactory;

    public function __construct(
        string $tmpDir,
        Differ $differ,
        PrettyPrinterAbstract $prettyPrinter,
        MutantCodeFactory $mutantCodeFactory
    ) {
        $this->tmpDir = $tmpDir;
        $this->differ = $differ;
        $this->prettyPrinter = $prettyPrinter;
        $this->mutantCodeFactory = $mutantCodeFactory;
    }

    public function create(Mutation $mutation): Mutant
    {
        $mutantFilePath = sprintf('%s/mutant.%s.infection.php', $this->tmpDir, $mutation->getHash());

        $mutantCode = $this->createMutantCode($mutation, $mutantFilePath);

        return new Mutant(
            $mutantFilePath,
            $mutation,
            $this->createMutantDiff($mutation, $mutantCode)
        );
    }

    private function createMutantCode(Mutation $mutation, string $mutantFilePath): string
    {
        if (is_readable($mutantFilePath)) {
            return file_get_contents($mutantFilePath);
        }

        $mutantCode = $this->mutantCodeFactory->createMutantCode($mutation);

        file_put_contents($mutantFilePath, $mutantCode);

        return $mutantCode;
    }

    private function createMutantDiff(Mutation $mutation, string $mutantCode): string
    {
        $originalPrettyPrintedFile = $this->getOriginalPrettyPrintedFile(
            $mutation->getOriginalFilePath(),
            $mutation->getOriginalFileAst()
        );

        return $this->differ->diff($originalPrettyPrintedFile, $mutantCode);
    }

    private function getOriginalPrettyPrintedFile(string $originalFilePath, array $originalStatements): string
    {
        return $this->prettyPrintedCache[$originalFilePath]
            ?? $this->prettyPrintedCache[$originalFilePath] = $this->prettyPrinter->prettyPrintFile($originalStatements);
    }
}
