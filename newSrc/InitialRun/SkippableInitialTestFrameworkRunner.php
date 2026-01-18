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

namespace newSrc\InitialRun;

use newSrc\Configuration;
use newSrc\Logger\Logger;
use newSrc\TestFramework\TestFramework;

final class SkippableInitialTestFrameworkRunner implements InitialTestFrameworkRunner
{
    public function __construct(
        private TestFramework $testFramework,
        private Configuration $configuration,
        private Logger $logger,
    ) {
    }

    public function run(): void
    {
        $frameworkName = $this->testFramework->getName();

        if ($this->testFramework->isSkippable() && $this->configuration->shouldSkipInitialTests($frameworkName)) {
            $this->logger->logSkippingInitialTests($frameworkName);

            // The test framework adapter does the coverage check – as it may differ from a test framework to another
            $this->testFramework->checkRequiredArtefacts();
        } else {
            // The test framework adapter does the initial run, rather than knowing about the details
            // We shouldn't need to get the output:
            // - logging can be handled within the test framework
            // - setting the memory limit will be test framework specific, e.g. PHPUnit may need 20MB, Behat 50.
            //   As such, it could simply set internal state as any process started by this test framework is handled by this test framework.
            $this->testFramework->executeInitialRun();
        }
    }
}
