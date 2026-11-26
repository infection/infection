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

namespace Infection\Tests\TestFramework\PhpUnit\Legacy;

use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\AbstractTestFramework\TestFrameworkAdapter;
use Infection\Mutant\DetectionStatus;
use Infection\TestFramework\Common\Process\DryRunProcessFactory;
use Infection\TestFramework\PhpUnit\Legacy\LegacyMutantProcessFactory;
use Infection\Tests\Mutant\MutantBuilder;
use Infection\Tests\Mutation\MutationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(LegacyMutantProcessFactory::class)]
final class LegacyMutantProcessFactoryTest extends TestCase
{
    public function test_it_uses_the_legacy_adapters_name_and_output_check(): void
    {
        $adapter = $this->createStub(TestFrameworkAdapter::class);
        $adapter->method('getName')->willReturn('Legacy framework');
        $adapter->method('testsPass')->willReturn(false);
        $factory = new LegacyMutantProcessFactory(new DryRunProcessFactory(), $adapter, 6.0);
        $mutation = MutationBuilder::withMinimalTestData()
            ->withTests([new TestLocation('FooTest::test_it_works', '/project/tests/FooTest.php', 1.0)])
            ->build()
        ;
        $mutant = MutantBuilder::withMinimalTestData()->withMutation($mutation)->build();

        $attempt = $factory->create(['php', 'tests.php'], $mutant);
        $attempt->markAsFinished();

        $this->assertSame($mutant, $attempt->getMutant());
        $this->assertSame('Legacy framework', $attempt->getTestFrameworkName());
        $this->assertSame(6.0, $attempt->getProcess()->getTimeout());
        $this->assertSame(DetectionStatus::KILLED_BY_TESTS, $attempt->getResult());
    }

    #[DataProvider('expectedTimeoutProvider')]
    public function test_it_compares_the_nominal_test_time_to_the_process_timeout(float $nominalTime, DetectionStatus $expected): void
    {
        $mutation = MutationBuilder::withMinimalTestData()
            ->withTests([new TestLocation('FooTest::test_it_works', '/project/tests/FooTest.php', $nominalTime)])
            ->build()
        ;
        $mutant = MutantBuilder::withMinimalTestData()->withMutation($mutation)->build();
        $factory = new LegacyMutantProcessFactory(
            new DryRunProcessFactory(),
            $this->createStub(TestFrameworkAdapter::class),
            10.0,
        );
        $attempt = $factory->create(['php', 'tests.php'], $mutant);
        $attempt->markAsTimedOut();
        $attempt->markAsFinished();

        $this->assertSame($expected, $attempt->getResult());
    }

    public static function expectedTimeoutProvider(): iterable
    {
        yield 'finishes within the limit' => [9.0, DetectionStatus::TIMED_OUT];

        yield 'takes the full limit' => [10.0, DetectionStatus::SKIPPED];

        yield 'exceeds the limit' => [11.0, DetectionStatus::SKIPPED];
    }
}
