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

namespace Infection\TestFramework\PhpUnit\Mutant;

use Infection\Mutant\Mutant;
use Infection\Process\MutantProcess;
use Infection\TestFramework\Common\CommandLineBuilder;
use Infection\TestFramework\Common\Process\ProcessFactory;
use Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use Infection\TestFramework\PhpUnit\Config\Builder\MutationConfigBuilder;
use Infection\TestFramework\PhpUnit\MemoryLimiter;
use function min;

/**
 * @internal
 */
final readonly class PHPUnitMutantProcessFactory
{
    private const int TIMEOUT_FACTOR = 5;

    private const int TEST_FRAMEWORK_BOOTSTRAP_THRESHOLD = 5;

    public function __construct(
        private string $testFrameworkName,
        private ProcessFactory $processFactory,
        private float $timeout,
        private string $testFrameworkExecutable,
        private CommandLineBuilder $commandLineBuilder,
        private MutationConfigBuilder $mutationConfigBuilder,
        private ArgumentsAndOptionsBuilder $argumentsAndOptionsBuilder,
        private MemoryLimiter $memoryLimiter,
    ) {
    }

    public function create(Mutant $mutant, string $version, string $extraOptions): MutantProcess
    {
        // The nominal time excludes test framework bootstrap and process startup.
        $testTime = $mutant->getMutation()->getNominalTestExecutionTime();
        $timeout = min(self::TEST_FRAMEWORK_BOOTSTRAP_THRESHOLD + self::TIMEOUT_FACTOR * $testTime, $this->timeout);

        $process = $this->processFactory->create(
            $this->getMutantCommandLine($mutant, $version, $extraOptions),
            timeout: $timeout,
        );

        return new MutantProcess(
            $process,
            $mutant,
            new PHPUnitMutantDetectionStatusResolver($testTime >= $timeout),
            $this->testFrameworkName,
        );
    }

    /**
     * @return string[]
     */
    private function getMutantCommandLine(Mutant $mutant, string $version, string $extraOptions): array
    {
        $tests = $mutant->getTests();
        $mutation = $mutant->getMutation();

        return $this->commandLineBuilder->build(
            $this->testFrameworkExecutable,
            $this->memoryLimiter->getPhpExtraArguments(),
            $this->argumentsAndOptionsBuilder->buildForMutant(
                $this->mutationConfigBuilder->build(
                    $tests,
                    $mutant->getFilePath(),
                    $mutation->getHash(),
                    $mutation->getOriginalFilePath(),
                    $version,
                ),
                $extraOptions,
                $tests,
                $version,
            ),
        );
    }
}
