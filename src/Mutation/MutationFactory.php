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

use function implode;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\Differ\Differ;
use Infection\PhpParser\MutatedNode;
use function md5;
use PhpParser\Node;
use PhpParser\PrettyPrinterAbstract;
use function Safe\sprintf;

/**
 * @internal
 * @final
 */
class MutationFactory
{
    private $tmpDir;
    private $differ;
    private $printer;

    /**
     * @var string[]
     */
    private $printedFileCache = [];
    private $mutationCodeFactory;

    public function __construct(
        string $tmpDir,
        Differ $differ,
        PrettyPrinterAbstract $printer,
        MutationCodeFactory $mutationCodeFactory
    ) {
        $this->tmpDir = $tmpDir;
        $this->differ = $differ;
        $this->printer = $printer;
        $this->mutationCodeFactory = $mutationCodeFactory;
    }

    /**
     * @param Node[] $originalFileAst
     * @param array<string|int|float> $attributes
     * @param class-string $mutatedNodeClass
     * @param TestLocation[] $tests
     */
    public function create(
        string $originalFilePath,
        array $originalFileAst,
        string $mutatorName,
        array $attributes,
        string $mutatedNodeClass,
        MutatedNode $mutatedNode,
        int $mutationByMutatorIndex,
        array $tests
    ): Mutation {
        return new Mutation(
            $originalFilePath,
            $mutatorName,
            $attributes,
            $tests,
            function () use (
                $originalFilePath,
                $originalFileAst,
                $mutatorName,
                $attributes,
                $mutatedNodeClass,
                $mutatedNode,
                $mutationByMutatorIndex
            ): MutationCalculatedState {
                return $this->calculateState(
                    $originalFilePath,
                    $originalFileAst,
                    $mutatorName,
                    $attributes,
                    $mutatedNodeClass,
                    $mutatedNode,
                    $mutationByMutatorIndex
                );
            }
        );
    }

    /**
     * @param Node[] $originalFileAst
     * @param array<string|int|float> $attributes
     * @param class-string $mutatedNodeClass
     */
    private function calculateState(
        string $originalFilePath,
        array $originalFileAst,
        string $mutatorName,
        array $attributes,
        string $mutatedNodeClass,
        MutatedNode $mutatedNode,
        int $mutationByMutatorIndex
    ): MutationCalculatedState {
        $hash = self::createHash(
            $originalFilePath,
            $mutatorName,
            $attributes,
            $mutationByMutatorIndex
        );

        $mutationFilePath = sprintf(
            '%s/mutation.%s.infection.php',
            $this->tmpDir,
            $hash
        );

        $mutatedCode = $this->mutationCodeFactory->createCode(
            $attributes,
            $originalFileAst,
            $mutatedNodeClass,
            $mutatedNode
        );

        return new MutationCalculatedState(
            $hash,
            $mutationFilePath,
            $mutatedCode,
            $this->createMutationDiff(
                $originalFilePath,
                $originalFileAst,
                $mutatedCode
            )
        );
    }

    /**
     * @param array<string|int|float> $attributes
     */
    private static function createHash(
        string $originalFilePath,
        string $mutatorName,
        array $attributes,
        int $mutationByMutatorIndex
    ): string {
        $hashKeys = [
            $originalFilePath,
            $mutatorName,
            $mutationByMutatorIndex,
        ];

        foreach ($attributes as $attribute) {
            $hashKeys[] = $attribute;
        }

        return md5(implode('_', $hashKeys));
    }

    /**
     * @param Node[] $originalFileAst
     */
    private function createMutationDiff(
        string $originalFilePath,
        array $originalFileAst,
        string $mutationCode
    ): string {
        $originalPrettyPrintedFile = $this->getOriginalPrettyPrintedFile(
            $originalFilePath,
            $originalFileAst
        );

        return $this->differ->diff($originalPrettyPrintedFile, $mutationCode);
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
