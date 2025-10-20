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

namespace Infection\Tests\Configuration\Options;

use Infection\Configuration\Options\CliOptionsApplier;
use Infection\Configuration\Options\InfectionOptions;
use Infection\Configuration\Options\SourceOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CliOptionsApplier::class)]
final class CliOptionsApplierTest extends TestCase
{
    private CliOptionsApplier $applier;

    protected function setUp(): void
    {
        $this->applier = new CliOptionsApplier();
    }

    public function test_it_does_not_override_with_null_values(): void
    {
        $options = new InfectionOptions(
            source: new SourceOptions(directories: ['src']),
            timeout: 10.0,
            threads: 4,
            minMsi: 80.0,
            testFramework: 'phpunit',
        );

        $this->applier->apply(
            $options,
            initialTestsPhpOptions: null,
            ignoreMsiWithNoMutations: null,
            minMsi: null,
            minCoveredMsi: null,
            testFramework: null,
            testFrameworkExtraOptions: null,
            staticAnalysisToolOptions: null,
            threadCount: null,
            staticAnalysisTool: null,
        );

        // Values should remain unchanged
        $this->assertSame(10.0, $options->timeout);
        $this->assertSame(4, $options->threads);
        $this->assertSame(80.0, $options->minMsi);
        $this->assertSame('phpunit', $options->testFramework);
    }

    public function test_it_applies_cli_overrides(): void
    {
        $options = new InfectionOptions(
            source: new SourceOptions(directories: ['src']),
            timeout: 10.0,
            threads: 4,
            minMsi: 80.0,
            minCoveredMsi: 85.0,
            testFramework: 'phpunit',
        );

        $this->applier->apply(
            $options,
            initialTestsPhpOptions: '--verbose',
            ignoreMsiWithNoMutations: true,
            minMsi: 90.0,
            minCoveredMsi: 95.0,
            testFramework: 'phpspec',
            testFrameworkExtraOptions: '--stop-on-failure',
            staticAnalysisToolOptions: '--memory-limit=2G',
            threadCount: 8,
            staticAnalysisTool: 'phpstan',
        );

        // CLI values should override config file values
        $this->assertSame('--verbose', $options->initialTestsPhpOptions);
        $this->assertTrue($options->ignoreMsiWithNoMutations);
        $this->assertSame(90.0, $options->minMsi);
        $this->assertSame(95.0, $options->minCoveredMsi);
        $this->assertSame('phpspec', $options->testFramework);
        $this->assertSame('--stop-on-failure', $options->testFrameworkOptions);
        $this->assertSame('--memory-limit=2G', $options->staticAnalysisToolOptions);
        $this->assertSame(8, $options->threads);
        $this->assertSame('phpstan', $options->staticAnalysisTool);
    }

    public function test_it_applies_partial_cli_overrides(): void
    {
        $options = new InfectionOptions(
            source: new SourceOptions(directories: ['src']),
            timeout: 10.0,
            threads: 4,
            minMsi: 80.0,
        );

        // Only some CLI args provided
        $this->applier->apply(
            $options,
            initialTestsPhpOptions: null,
            ignoreMsiWithNoMutations: null,
            minMsi: 85.0,  // Override
            minCoveredMsi: null,
            testFramework: null,
            testFrameworkExtraOptions: null,
            staticAnalysisToolOptions: null,
            threadCount: 12,  // Override
            staticAnalysisTool: null,
        );

        // Only specified CLI values override
        $this->assertSame(10.0, $options->timeout);  // Unchanged
        $this->assertSame(85.0, $options->minMsi);    // Overridden
        $this->assertSame(12, $options->threads);     // Overridden
    }
}
