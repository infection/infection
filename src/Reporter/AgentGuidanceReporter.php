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

namespace Infection\Reporter;

use Infection\Metrics\MetricsCalculator;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final readonly class AgentGuidanceReporter implements Reporter
{
    private const string GUIDANCE = <<<'TXT'
        When dealing with escaped mutations, consider applicable techniques:

        Assertion Amplification, Boundary Value Analysis, Equivalence Partitioning, State Verification, Strict Assertions,
        RIPR (Reachability, Infection, Propagation, Revealability), Property-Based Testing, Parameterized Tests, Decision Table
        Testing, MC/DC, Behavioral Contracts, Independent Test Oracles, Invariants, Behavior Verification, Test Seams,
        Test Stubs, Test Spies, Effect Analysis, Metamorphic Testing, State Transition Testing, Exception Verification,
        Delta Assertions, Guard Assertions, Dependency Injection, Fake Objects, Mock Objects, Fault Injection, Extract and
        Override Call, Pinch Points, Combinatorial Testing, Differential Testing, Test Isolation, Humble Object,
        Pure Functions, Functional Core/Imperative Shell.

        Prefer minimal, behavior-preserving changes, meaningful behavioral assertions, and strengthening existing tests.
        TXT;

    public function __construct(
        private OutputInterface $output,
        private MetricsCalculator $metricsCalculator,
    ) {
    }

    public function report(): void
    {
        if ($this->metricsCalculator->getEscapedCount() === 0) {
            return;
        }

        $this->output->writeln(['', self::GUIDANCE]);
    }
}
