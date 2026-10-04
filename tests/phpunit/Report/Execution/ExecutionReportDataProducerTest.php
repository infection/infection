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

namespace Infection\Tests\Report\Execution;

use function array_filter;
use function array_map;
use function explode;
use function hash;
use Infection\AbstractTestFramework\Coverage\TestLocation;
use Infection\FileSystem\FileSystem;
use Infection\Report\Execution\ExecutionReportDataProducer;
use Infection\TestFramework\Tracing\Throwable\NoTraceFound;
use Infection\TestFramework\Tracing\Trace\TestLocations;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\TestFramework\Tracing\Tracer;
use Infection\Tests\Mutant\MutantExecutionResultBuilder;
use function json_decode;
use const JSON_THROW_ON_ERROR;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use function trim;

#[CoversClass(ExecutionReportDataProducer::class)]
final class ExecutionReportDataProducerTest extends TestCase
{
    public function test_it_records_execution_evidence_and_cache_changes_without_inferred_killing_tests(): void
    {
        $initialXml = '<phpunit cacheDirectory="/cache" recordTestImpactData="true"/>';
        $mutantXml = '<phpunit recordTestImpactData="false"/>';
        $files = [
            '/tmp/phpunitConfiguration.initial.infection.xml' => $initialXml,
            '/tmp/phpunit-impact-sources.txt' => "/src/Calculator.php\n",
            '/cache/test-impact-data' => 'old impact',
            '/cache/test-run-history' => 'old history',
            '/tmp/junit.xml' => '<testsuites><testsuite><testcase class="CalculatorTest" name="test_add with data set &quot;positive&quot;" file="/tests/CalculatorTest.php"/><testcase class="UnrelatedTest" name="test_skipped" file="/tests/UnrelatedTest.php"><skipped/></testcase></testsuite></testsuites>',
            '/tmp/phpunitConfiguration.abc123def456.infection.xml' => $mutantXml,
        ];
        $filesystem = $this->createStub(FileSystem::class);
        $filesystem->method('isReadableFile')->willReturnCallback(static fn (string $path): bool => isset($files[$path]));
        $filesystem->method('readFile')->willReturnCallback(static function (string $path) use (&$files): string { return $files[$path]; });
        $trace = $this->createStub(Trace::class);
        $trace->method('getTests')->willReturn(new TestLocations([
            8 => [TestLocation::forTestMethod('ZTest::test_z'), TestLocation::forTestMethod('CalculatorTest::test_add'), TestLocation::forTestMethod('CalculatorTest::test_add')],
        ]));
        $tracer = $this->createMock(Tracer::class);
        $tracer->expects($this->once())->method('trace')->willReturnCallback(function (SplFileInfo $file) use ($trace): Trace {
            $this->assertSame('/src/Calculator.php', $file->getPathname());

            return $trace;
        });
        $reporter = new ExecutionReportDataProducer($filesystem, $tracer, '/tmp', '/tmp/junit.xml');

        $reporter->start();
        $reporter->recordInitialStart('initial command');
        $files['/cache/test-impact-data'] = 'new impact';
        $files['/cache/test-run-history'] = 'new history';
        $reporter->recordInitialFinish('initial output');
        $reporter->recordSource('/src/Calculator.php', ['abc123def456']);
        $result = MutantExecutionResultBuilder::withMinimalTestData()
            ->withProcessCommandLine('mutant command')
            ->withProcessOutput("assertion failed\xff")
            ->withTests([TestLocation::forTestMethod('CalculatorTest::test_add')])
            ->build()
        ;
        $reporter->recordMutant($result);
        $reporter->finish();
        /** @var list<array<string, mixed>> $records */
        $records = array_map(static fn (string $line) => json_decode($line, true, flags: JSON_THROW_ON_ERROR), explode("\n", trim($reporter->produce())));

        $this->assertSame(['event' => 'execution_started', 'formatVersion' => 1], $records[0]);
        $this->assertSame([
            'event' => 'initial_tests_started',
            'commandLine' => 'initial command',
            'configuration' => $initialXml,
            'impactQuery' => "/src/Calculator.php\n",
            'configuredCache' => ['directory' => '/cache', 'test-impact-data' => hash('sha256', 'old impact'), 'test-run-history' => hash('sha256', 'old history')],
        ], $records[1]);
        $this->assertSame([
            'event' => 'initial_tests_finished',
            'output' => 'initial output',
            'testCases' => [
                ['class' => 'CalculatorTest', 'name' => 'test_add with data set "positive"', 'file' => '/tests/CalculatorTest.php', 'skipped' => false],
                ['class' => 'UnrelatedTest', 'name' => 'test_skipped', 'file' => '/tests/UnrelatedTest.php', 'skipped' => true],
            ],
            'configuredCache' => ['directory' => '/cache', 'test-impact-data' => hash('sha256', 'new impact'), 'test-run-history' => hash('sha256', 'new history')],
        ], $records[2]);
        $this->assertSame([
            'event' => 'source_processed',
            'file' => '/src/Calculator.php',
            'mutationHashes' => ['abc123def456'],
            'coverage' => [['line' => 8, 'tests' => ['CalculatorTest::test_add', 'ZTest::test_z']]],
        ], $records[3]);
        $this->assertSame([
            'event' => 'mutant_finished',
            'hash' => 'abc123def456',
            'file' => 'src/Foo.php',
            'mutator' => 'For_',
            'startLine' => 10,
            'endLine' => 15,
            'status' => 'killed by tests',
            'diff' => '--- Original' . "\n+++ Mutated\n@@ @@\n-" . '$a = 1;' . "\n+" . '$a = 2;',
            'coveringTests' => ['CalculatorTest::test_add'],
            'commandLine' => 'mutant command',
            'output' => "assertion failed\u{FFFD}",
            'configuration' => $mutantXml,
        ], $records[4]);
        $this->assertSame(['event' => 'mutation_testing_finished', 'configuredCache' => ['directory' => '/cache', 'test-impact-data' => hash('sha256', 'new impact'), 'test-run-history' => hash('sha256', 'new history')]], $records[5]);
    }

    /**
     * @param list<array{class: string, name: string, file: string, skipped: bool}>|null $testCases
     * @param array<string, string|null>|null $cache
     */
    #[DataProvider('unavailableArtefactsProvider')]
    public function test_it_distinguishes_missing_reports_from_an_empty_test_selection(?string $junit, ?string $configuration, ?array $testCases, ?array $cache): void
    {
        /** @var array<string, string> $files */
        $files = array_filter(['/tmp/junit.xml' => $junit, '/tmp/phpunitConfiguration.initial.infection.xml' => $configuration], static fn (?string $value) => $value !== null);
        $filesystem = $this->createStub(FileSystem::class);
        $filesystem->method('isReadableFile')->willReturnCallback(static fn (string $path): bool => isset($files[$path]));
        $filesystem->method('readFile')->willReturnCallback(static fn (string $path): string => $files[$path]);
        $reporter = new ExecutionReportDataProducer($filesystem, $this->createStub(Tracer::class), '/tmp', '/tmp/junit.xml');

        $reporter->recordInitialStart('command');
        $reporter->recordInitialFinish('output');
        /** @var list<array<string, mixed>> $records */
        $records = array_map(static fn (string $line) => json_decode($line, true, flags: JSON_THROW_ON_ERROR), explode("\n", trim($reporter->produce())));

        $this->assertSame($configuration, $records[0]['configuration']);
        $this->assertSame($testCases, $records[1]['testCases']);
        $this->assertSame($cache, $records[1]['configuredCache']);
    }

    public static function unavailableArtefactsProvider(): iterable
    {
        yield 'missing artifacts' => [null, null, null, null];

        yield 'incomplete artifacts after a failure' => ['<testsuites>', '<phpunit', null, null];

        yield 'empty report and no cache configured' => ['<testsuites/>', '<phpunit/>', [], null];

        yield 'configured cache files do not exist' => ['<testsuites/>', '<phpunit cacheDirectory="../cache"/>', [], ['directory' => '/cache', 'test-impact-data' => null, 'test-run-history' => null]];
    }

    public function test_it_records_uncovered_source_without_creating_a_trace(): void
    {
        $filesystem = $this->createStub(FileSystem::class);
        $tracer = $this->createMock(Tracer::class);
        $tracer->expects($this->once())->method('trace')->willThrowException(new NoTraceFound('No trace'));

        $producer = new ExecutionReportDataProducer($filesystem, $tracer, '/tmp', '/tmp/junit.xml');
        $producer->recordSource('/src/Uncovered.php', []);
        $this->assertSame(['event' => 'source_processed', 'file' => '/src/Uncovered.php', 'mutationHashes' => [], 'coverage' => []], json_decode($producer->produce(), true, flags: JSON_THROW_ON_ERROR));
    }
}
