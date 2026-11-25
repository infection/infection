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

namespace Infection\Tests\Testing\TestFramework\Debug;

use Infection\Mutant\DetectionStatus;
use Infection\Testing\TestFramework\Debug\FixedMutantProcessDetectionStatusResolver;
use Infection\Tests\TestFramework\Mutant\DetectionStatusScenario;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(FixedMutantProcessDetectionStatusResolver::class)]
final class FixedMutantProcessDetectionStatusResolverTest extends TestCase
{
    #[DataProvider('processOutcomesProvider')]
    public function test_it_returns_the_configured_status_regardless_of_the_process_outcome(DetectionStatusScenario $scenario): void
    {
        $resolver = new FixedMutantProcessDetectionStatusResolver($scenario->expected);

        $actual = $resolver->resolve($scenario->stdout, $scenario->exitCode, $scenario->timedOut);

        $this->assertSame($scenario->expected, $actual);
    }

    public static function processOutcomesProvider(): iterable
    {
        $baseScenario = DetectionStatusScenario::withTestData();

        yield 'successful process with a fixed kill' => [
            $baseScenario->withExpected(DetectionStatus::KILLED_BY_STATIC_ANALYSIS),
        ];

        yield 'timed out process with a fixed kill' => [
            $baseScenario
                ->withStdout('output')
                ->withExitCode(null)
                ->withTimedOut(true)
                ->withExpected(DetectionStatus::KILLED_BY_STATIC_ANALYSIS),
        ];

        yield 'failed process with a fixed escape' => [
            $baseScenario
                ->withExitCode(101)
                ->withExpected(DetectionStatus::ESCAPED),
        ];
    }
}
