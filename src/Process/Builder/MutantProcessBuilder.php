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

namespace Infection\Process\Builder;

use Infection\Mutant\Mutant;
use Infection\Process\MutantProcess;
use Infection\TestFramework\TestFrameworkAdapter;
use Infection\Utils\VersionParser;
use PackageVersions\Versions;
use Symfony\Component\Process\Process;

/**
 * @internal
 * @final
 */
class MutantProcessBuilder
{
    private $testFrameworkAdapter;
    private $timeout;
    private $versionParser;

    public function __construct(TestFrameworkAdapter $testFrameworkAdapter, VersionParser $versionParser, int $timeout)
    {
        $this->testFrameworkAdapter = $testFrameworkAdapter;
        $this->timeout = $timeout;
        $this->versionParser = $versionParser;
    }

    public function createProcessForMutant(Mutant $mutant, string $testFrameworkExtraOptions = ''): MutantProcess
    {
        $process = new Process(
            $this->testFrameworkAdapter->getMutantCommandLine(
                $mutant->getTests(),
                $mutant->getMutantFilePath(),
                $mutant->getMutation()->getHash(),
                $mutant->getMutation()->getOriginalFilePath(),
                $testFrameworkExtraOptions
            )
        );

        $process->setTimeout($this->timeout);

        $symfonyProcessVersion = $this->versionParser->parse(Versions::getVersion('symfony/process'));

        if (version_compare($symfonyProcessVersion, '4.4.0', '<')) {
            // in version 4.4.0 this method is deprecated and removed in 5.0.0
            $process->inheritEnvironmentVariables();
        }

        return new MutantProcess($process, $mutant, $this->testFrameworkAdapter);
    }
}
