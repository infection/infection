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

namespace Infection\TestFramework\Contracts;

use Infection\Mutant\Mutant;
use Infection\Process\MutantProcessContainer;
use Infection\TestFramework\Contracts\Throwable\InitialTestsFailed;
use Infection\TestFramework\Contracts\Throwable\RequirementChecksFailed;

/**
 * A test framework is the tool that will be used to evaluate mutations to check if they are
 * covered.
 *
 * It can be a standard test framework such as PHPUnit, PhpSpec, a static analyser like PHPStan
 * or Psalm or something else entirely!
 *
 * @internal
 */
interface TestFramework
{
    /**
     * Name of the test framework, e.g. "PHPUnit" or "PHPStan".
     */
    public function getName(): string;

    /**
     * Checks that the version of the tool used is compatible with the adapter.
     *
     * Additionally, Some test frameworks may require artefacts to work with. For example, PHPUnit
     * requires a code coverage report. PHPStan will require an up-to-date cache.
     *
     * @throws RequirementChecksFailed
     */
    public function checkRequirements(): void;

    /**
     * Initial test run. This allows tools like PHPUnit to ensure the tests are valid
     * in the first place and generate the required code coverage or PHPStan to generate
     * an up-to-date cache.
     *
     * @throws InitialTestsFailed
     */
    public function executeInitialRun(): InitialRunResults;

    /**
     * Evaluates the Mutant. Some test frameworks may be able to do this in-memory, e.g.
     * Psalm, or it requires to launch a process in which case the process execution is
     * delegated to an orchestrator. A process encapsulates how the result of the process
     * is interpreted.
     */
    public function test(Mutant $mutant): MutantProcessContainer;
}
