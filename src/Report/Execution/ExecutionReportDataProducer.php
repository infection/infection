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

namespace Infection\Report\Execution;

use function array_unique;
use function array_values;
use DOMElement;
use function hash;
use function implode;
use Infection\FileSystem\FileSystem;
use Infection\Mutant\MutantExecutionResult;
use Infection\Report\Framework\DataProducer;
use Infection\TestFramework\Tracing\Throwable\NoTraceFound;
use Infection\TestFramework\Tracing\Tracer;
use Infection\TestFramework\XML\InvalidXml;
use Infection\TestFramework\XML\SafeDOMXPath;
use function json_encode;
use const JSON_INVALID_UTF8_SUBSTITUTE;
use const JSON_THROW_ON_ERROR;
use Override;
use function sort;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;

/**
 * Collects execution facts without retaining ASTs or process containers.
 * Only enabled diagnostic runs pay for reading artefacts.
 * Covering tests are candidates for mutant execution, not identified killing tests.
 *
 * @internal
 * @final
 */
class ExecutionReportDataProducer implements DataProducer
{
    private const int FORMAT_VERSION = 1;

    private ?string $cacheDirectory = null;

    /** @var list<string> */
    private array $records = [];

    public function __construct(
        private readonly FileSystem $filesystem,
        private readonly Tracer $tracer,
        private readonly string $tmpDir,
        private readonly string $junitPath,
    ) {
    }

    public function start(): void
    {
        $this->cacheDirectory = null;
        $this->records = [];
        $this->write('execution_started', ['formatVersion' => self::FORMAT_VERSION]);
    }

    public function recordInitialStart(string $commandLine): void
    {
        $configurationPath = $this->tmpDir . '/phpunitConfiguration.initial.infection.xml';
        $configuration = $this->readOptionalFile($configurationPath);

        $xpath = self::parseXml($configuration);

        if ($xpath !== null) {
            $root = $xpath->getElement('/phpunit');
            $cacheDirectory = $root->getAttribute('cacheDirectory');

            if ($cacheDirectory !== '') {
                $this->cacheDirectory = Path::makeAbsolute($cacheDirectory, $this->tmpDir);
            }
        }

        $this->write('initial_tests_started', [
            'commandLine' => $commandLine,
            'configuration' => $configuration,
            'impactQuery' => $this->readOptionalFile($this->tmpDir . '/phpunit-impact-sources.txt'),
            'configuredCache' => $this->cacheSnapshot(),
        ]);
    }

    public function recordInitialFinish(string $output): void
    {
        $junit = $this->readOptionalFile($this->junitPath);
        $tests = null;

        $xpath = self::parseXml($junit);

        if ($xpath !== null) {
            $tests = [];

            foreach ($xpath->queryList('//testcase') as $test) {
                if (!$test instanceof DOMElement) {
                    continue;
                }

                $tests[] = [
                    'class' => $test->getAttribute('class'),
                    'name' => $test->getAttribute('name'),
                    'file' => $test->getAttribute('file'),
                    'skipped' => $test->getElementsByTagName('skipped')->length !== 0,
                ];
            }
        }

        $this->write('initial_tests_finished', [
            'output' => $output,
            'testCases' => $tests,
            'configuredCache' => $this->cacheSnapshot(),
        ]);
    }

    /** @param list<string> $mutationHashes */
    public function recordSource(string $sourceFilePath, array $mutationHashes): void
    {
        $coverage = [];

        try {
            $trace = $this->tracer->trace(new SplFileInfo($sourceFilePath));

            foreach ($trace->getTests()->getTestsLocationsBySourceLine() as $line => $tests) {
                $methods = [];

                foreach ($tests as $test) {
                    $methods[] = $test->getMethod();
                }

                $methods = array_values(array_unique($methods));
                sort($methods);
                $coverage[] = ['line' => $line, 'tests' => $methods];
            }
        } catch (NoTraceFound) {
            // Match mutation generation: a missing trace represents uncovered source.
        }

        $this->write('source_processed', [
            'file' => $sourceFilePath,
            'mutationHashes' => $mutationHashes,
            'coverage' => $coverage,
        ]);
    }

    public function recordMutant(MutantExecutionResult $result): void
    {
        $tests = [];

        foreach ($result->getTests() as $test) {
            $tests[] = $test->getMethod();
        }

        $this->write('mutant_finished', [
            'hash' => $result->getMutantHash(),
            'file' => $result->getOriginalFilePath(),
            'mutator' => $result->getMutatorName(),
            'startLine' => $result->getOriginalStartingLine(),
            'endLine' => $result->getOriginalEndingLine(),
            'status' => $result->getDetectionStatus()->value,
            'diff' => $result->getMutantDiff(),
            'coveringTests' => $tests,
            'commandLine' => $result->getProcessCommandLine(),
            'output' => $result->getProcessOutput(),
            'configuration' => $this->readOptionalFile(
                $this->tmpDir . '/phpunitConfiguration.' . $result->getMutantHash() . '.infection.xml',
            ),
        ]);
    }

    public function finish(): void
    {
        $this->write('mutation_testing_finished', ['configuredCache' => $this->cacheSnapshot()]);
    }

    #[Override]
    public function produce(): string
    {
        return implode("\n", $this->records) . "\n";
    }

    /** @return array<string, string|null>|null */
    private function cacheSnapshot(): ?array
    {
        $cacheDirectory = $this->cacheDirectory;

        if ($cacheDirectory === null) {
            return null;
        }

        $snapshot = ['directory' => $cacheDirectory];

        foreach (['test-impact-data', 'test-run-history'] as $name) {
            $content = $this->readOptionalFile($cacheDirectory . '/' . $name);
            $snapshot[$name] = $content === null ? null : hash('sha256', $content);
        }

        return $snapshot;
    }

    private static function parseXml(?string $xml): ?SafeDOMXPath
    {
        if ($xml === null) {
            return null;
        }

        try {
            return SafeDOMXPath::fromString($xml);
        } catch (InvalidXml) {
            // A failed initial run may leave incomplete XML. Retain its output
            // instead of replacing the test failure with a reporting failure.
            return null;
        }
    }

    private function readOptionalFile(string $path): ?string
    {
        if (!$this->filesystem->isReadableFile($path)) {
            return null;
        }

        return $this->filesystem->readFile($path);
    }

    /** @param array<string, mixed> $data */
    private function write(string $event, array $data): void
    {
        $this->records[] = json_encode(['event' => $event, ...$data], JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
