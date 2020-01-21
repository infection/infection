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

namespace Infection\TestFramework\Codeception\Adapter;

use Infection\TestFramework\Codeception\Stringifier;
use Infection\TestFramework\CommandLineBuilder;
use Infection\TestFramework\Coverage\CoverageLineData;
use Infection\TestFramework\Coverage\XmlReport\JUnitTestCaseSorter;
use Infection\TestFramework\MemoryUsageAware;
use Infection\TestFramework\TestFrameworkAdapter;
use Infection\TestFramework\TestFrameworkTypes;
use Infection\TestFramework\VersionParser;
use InvalidArgumentException;
use Phar;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use function array_key_exists;
use function assert;
use function dirname;
use function is_string;
use function Safe\file_put_contents;

/**
 * @internal
 */
final class CodeceptionAdapter implements MemoryUsageAware, TestFrameworkAdapter
{
    public const EXECUTABLE = 'codecept';

    public const COVERAGE_DIR = 'codeception-coverage-xml';

    private const DEFAULT_ARGS_AND_OPTIONS = [
        'run',
        '--no-colors',
        '--fail-fast',
    ];

    private $testFrameworkExecutable;
    private $commandLineBuilder;
    private $versionParser;
    private $jUnitTestCaseSorter;
    private $filesystem;
    private $jUnitFilePath;
    private $tmpDir;
    private $projectDir;
    private $originalConfigContentParsed;
    private $srcDirs;

    /**
     * @var string|null
     */
    private $cachedVersion;

    /**
     * @param array<string, mixed> $originalConfigContentParsed
     */
    public function __construct(
        string $testFrameworkExecutable,
        CommandLineBuilder $commandLineBuilder,
        VersionParser $versionParser,
        JUnitTestCaseSorter $jUnitTestCaseSorter,
        Filesystem $filesystem,
        string $jUnitFilePath,
        string $tmpDir,
        string $projectDir,
        array $originalConfigContentParsed,
        array $srcDirs
    ) {
        $this->commandLineBuilder = $commandLineBuilder;
        $this->testFrameworkExecutable = $testFrameworkExecutable;
        $this->versionParser = $versionParser;
        $this->jUnitFilePath = $jUnitFilePath;
        $this->tmpDir = $tmpDir;
        $this->jUnitTestCaseSorter = $jUnitTestCaseSorter;
        $this->filesystem = $filesystem;
        $this->projectDir = $projectDir;
        $this->originalConfigContentParsed = $originalConfigContentParsed;
        $this->srcDirs = $srcDirs;
    }

    public function hasJUnitReport(): bool
    {
        return true;
    }

    public function testsPass(string $output): bool
    {
        if (preg_match('/failures!/i', $output)) {
            return false;
        }

        if (preg_match('/errors!/i', $output)) {
            return false;
        }

        // OK (XX tests, YY assertions)
        $isOk = preg_match('/OK\s\(/', $output);

        // "OK, but incomplete, skipped, or risky tests!"
        $isOkWithInfo = preg_match('/OK\s?,/', $output);

        // "Warnings!" - e.g. when deprecated functions are used, but tests pass
        $isWarning = preg_match('/warnings!/i', $output);

        return $isOk || $isOkWithInfo || $isWarning;
    }

    public function getMemoryUsed(string $output): float
    {
        if (preg_match('/Memory: (\d+(?:\.\d+))\s*MB/', $output, $match)) {
            return (float) $match[1];
        }

        return -1;
    }

    public function getName(): string
    {
        return TestFrameworkTypes::CODECEPTION;
    }

    public function getInitialTestRunCommandLine(string $extraOptions, array $phpExtraArgs, bool $skipCoverage): array
    {
        $argumentsAndOptions = $this->prepareArgumentsAndOptions($extraOptions);

        return $this->commandLineBuilder->build(
            $this->testFrameworkExecutable,
            $phpExtraArgs,
            array_merge(
                $argumentsAndOptions,
                [
                    '--coverage-phpunit',
                    self::COVERAGE_DIR,
                    // JUnit report
                    '--xml',
                    $this->jUnitFilePath,
                    '-o',
                    "paths: output: {$this->tmpDir}",
                    '-o',
                    sprintf('coverage: enabled: %s', Stringifier::stringifyBoolean(!$skipCoverage)),
                    '-o',
                    sprintf('coverage: include: %s', $this->getCoverageIncludeFiles($skipCoverage)),
                    '-o',
                    'settings: shuffle: true',
                ]
            )
        );
    }

    /**
     * @param CoverageLineData[] $coverageTests
     *
     * @return string[]
     */
    public function getMutantCommandLine(
        array $coverageTests,
        string $mutantFilePath,
        string $mutationHash,
        string $mutationOriginalFilePath,
        string $extraOptions
    ): array {
        $argumentsAndOptions = $this->prepareArgumentsAndOptions($extraOptions);

        $commandLine = $this->commandLineBuilder->build($this->testFrameworkExecutable, [], $argumentsAndOptions);

        $output = sprintf('%s/%s', $this->tmpDir, $mutationHash);

        $interceptorFilePath = sprintf(
            '%s/interceptor.codeception.%s.php',
            $this->tmpDir,
            $mutationHash
        );

        file_put_contents($interceptorFilePath, $this->createCustomBootstrapWithInterceptor($mutationOriginalFilePath, $mutantFilePath), LOCK_EX);

        $uniqueTestFilePaths = implode(',', $this->jUnitTestCaseSorter->getUniqueSortedFileNames($coverageTests));

        return array_merge(
            $commandLine,
            [
                '--group',
                'infection',
                '--bootstrap',
                $interceptorFilePath,
                '-o',
                "paths: output: {$output}",
                '-o',
                'coverage: enabled: false',
                '-o',
                "bootstrap: {$interceptorFilePath}",
                '-o',
                "groups: infection: [$uniqueTestFilePaths]",
            ]
        );
    }

    public function getVersion(): string
    {
        if ($this->cachedVersion !== null) {
            return $this->cachedVersion;
        }

        $testFrameworkVersionExecutable = $this->commandLineBuilder->build(
            $this->testFrameworkExecutable,
            [],
            ['--version']
        );

        $process = new Process($testFrameworkVersionExecutable);
        $process->mustRun();

        $version = 'unknown';

        try {
            $version = $this->versionParser->parse($process->getOutput());
        } catch (InvalidArgumentException $e) {
            $version = 'unknown';
        } finally {
            $this->cachedVersion = $version;
        }

        return $this->cachedVersion;
    }

    public function getInitialTestsFailRecommendations(string $commandLine): string
    {
        return sprintf('Check the executed command to identify the problem: %s', $commandLine);
    }

    protected function getInterceptorFileContent(string $interceptorPath, string $originalFilePath, string $mutantFilePath): string
    {
        $infectionPhar = '';

        if (strpos(__FILE__, 'phar:') === 0) {
            $infectionPhar = sprintf(
                '\Phar::loadPhar("%s", "%s");',
                str_replace('phar://', '', Phar::running(true)),
                'infection.phar'
            );
        }

        $namespacePrefix = $this->getInterceptorNamespacePrefix();

        return <<<CONTENT
{$infectionPhar}
require_once '{$interceptorPath}';

use {$namespacePrefix}Infection\StreamWrapper\IncludeInterceptor;

IncludeInterceptor::intercept('{$originalFilePath}', '{$mutantFilePath}');
IncludeInterceptor::enable();
CONTENT;
    }

    private function createCustomBootstrapWithInterceptor(string $originalFilePath, string $mutantFilePath): string
    {
        $originalBootstrap = $this->getOriginalBootstrapFilePath();
        $bootstrapPlaceholder = $originalBootstrap ? "require_once '{$originalBootstrap}';" : '';

        $interceptorPath = dirname(__DIR__, 3) . '/StreamWrapper/IncludeInterceptor.php';

        $customBootstrap = <<<AUTOLOAD
<?php

%s
%s

AUTOLOAD;

        return sprintf(
            $customBootstrap,
            $bootstrapPlaceholder,
            $this->getInterceptorFileContent($interceptorPath, $originalFilePath, $mutantFilePath)
        );
    }

    private function getOriginalBootstrapFilePath(): ?string
    {
        if (!array_key_exists('bootstrap', $this->originalConfigContentParsed)) {
            return null;
        }

        if ($this->filesystem->isAbsolutePath($this->originalConfigContentParsed['bootstrap'])) {
            return $this->originalConfigContentParsed['bootstrap'];
        }

        return sprintf(
            '%s/%s/%s',
            $this->projectDir,
            $this->originalConfigContentParsed['paths']['tests'] ?? 'tests',
            $this->originalConfigContentParsed['bootstrap']
        );
    }

    private function getInterceptorNamespacePrefix(): string
    {
        $prefix = strstr(__NAMESPACE__, 'Infection', true);
        assert(is_string($prefix));

        return $prefix;
    }

    /**
     * @return string[]
     */
    private function prepareArgumentsAndOptions(string $extraOptions): array
    {
        return array_filter(array_merge(
            explode(' ', $extraOptions),
            self::DEFAULT_ARGS_AND_OPTIONS
        ));
    }

    private function getCoverageIncludeFiles(bool $skipCoverage): string
    {
        // if coverage should be skipped, this anyway will be ignored, return early
        if ($skipCoverage) {
            return Stringifier::stringifyArray([]);
        }

        $coverage = array_merge($this->originalConfigContentParsed['coverage'] ?? [], ['enabled' => true]);

        $includedFiles = array_key_exists('include', $coverage)
            ? $coverage['include']
            : array_map(
                static function ($dir) {
                    return trim($dir, '/') . '/*.php';
                },
                $this->srcDirs
            );

        return Stringifier::stringifyArray($includedFiles);
    }
}
