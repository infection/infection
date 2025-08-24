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

namespace newSrc\Engine;

use Closure;
use newSrc\AST\ASTCollector;
use newSrc\Configuration;
use newSrc\InitialRun\InitialExecutionRunner;
use newSrc\Mutagenesis\Mutagenesis;
use newSrc\Mutagenesis\Mutation;
use newSrc\MutationAnalyzer\MutantExecutionResult;
use newSrc\MutationAnalyzer\MutationAnalyzer;
use newSrc\Reporter\Reporter;
use function Pipeline\take;
use SplFileInfo;

// Taken from the existing Engine
final readonly class Engine
{
    public function __construct(
        private Configuration $configuration,
        private InitialExecutionRunner $initialExecutionRunner,
        private ASTCollector $astCollector,
        private Mutagenesis $mutagenesis,
        private MutationAnalyzer $mutationAnalyzer,
        private Reporter $reporter,
        private MsiChecker $msiChecker,
    ) {
    }

    public function execute(): void
    {
        // Unlike the original, we do not execute the "initial test suite + initial static analysis"
        // but instead we execute the initial run, which contains any supported test framework
        $this->initialExecutionRunner->run();

        $this->executeMutationTesting();

        try {
            $this->msiChecker->check();
        } finally {
            $this->reporter->report();
        }
    }

    private function executeMutationTesting(): void
    {
        // Rather than getting the sources from the traces, we get the sources
        // from the configuration.
        // The configuration can use the Tracer to avoid including in the source
        // files that are not covered at all.

        // infection.json5
        // "source"
        //
        // CLI options: --git-diff or --filter
        //
        // coverage reports:
        // - PHPUnit XML + JUnit
        // - Behat Coverage Report
        //
        // =>
        //
        // $ infection --filter=X
        // -> source files = source AND X
        // -> initial run with coverage = source files
        //
        //
        // Currently:
        // $traces = $this->traceProvider->provideTraces();
        //

        // Without envelope
        take($this->configuration->getSourceFiles())
            ->map($this->astCollector->collect(...))
            ->unpack($this->mutagenesis->generate(...))
            ->map($this->mutationAnalyzer->analyze(...))
            ->map($this->reporter->collect(...))
            ->toList();

        // With envelope
        take($this->configuration->getSourceFiles())
            ->map(static fn (SplFileInfo $sourceFile) => [
                $sourceFile,
                Envelope::create($sourceFile),
            ])
            ->unpack(
                $this->mapWithEnvelope(
                    $this->astCollector->collect(...),
                    static fn (Envelope $envelope, array $ast) => $envelope->withAst($ast),
                ),
            )
            ->unpack(
                $this->mapWithEnvelope(
                    $this->mutagenesis->generate(...),
                    static fn (Envelope $envelope, Mutation $mutation) => $envelope->forMutation($mutation),
                ),
            )
            ->unpack(
                $this->mapWithEnvelope(
                    $this->mutationAnalyzer->analyze(...),
                    static fn (Envelope $envelope, MutantExecutionResult $mutation) => $envelope->withResult($mutation),
                ),
            )
            ->unpack($this->reporter->collect(...))
            ->toList();
    }

    /**
     * @template Input
     * @template Output
     *
     * @param Closure(Input): Output $map
     * @param Closure(Envelope, Output): Envelope $mapEnvelope
     *
     * @return Closure(Input, Envelope): array{Output, Envelope}
     */
    private function mapWithEnvelope(
        Closure $map,
        Closure $mapEnvelope,
    ): Closure {
        return static function (mixed $input, Envelope $envelope) use ($map, $mapEnvelope) {
            $output = $map($input);

            $newEnvelope = $mapEnvelope($envelope, $output);

            return [$output, $newEnvelope];
        };
    }
}
