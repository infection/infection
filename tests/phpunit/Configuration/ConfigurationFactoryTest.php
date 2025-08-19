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

namespace Infection\Tests\Configuration;

use Infection\Configuration\ConfigurationFactory;
use Infection\Configuration\Entry\Logs;
use Infection\Configuration\Entry\PhpStan;
use Infection\Configuration\Entry\PhpUnit;
use Infection\Configuration\Entry\Source;
use Infection\Configuration\Entry\StrykerConfig;
use Infection\Configuration\Schema\SchemaConfiguration;
use Infection\Console\LogVerbosity;
use Infection\FileSystem\SourceFileCollector;
use Infection\FileSystem\TmpDirProvider;
use Infection\Logger\GitHub\GitDiffFileProvider;
use Infection\Mutator\Arithmetic\AssignmentEqual;
use Infection\Mutator\Boolean\EqualIdentical;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\Boolean\TrueValueConfig;
use Infection\Mutator\IgnoreConfig;
use Infection\Mutator\IgnoreMutator;
use Infection\Mutator\Mutator;
use Infection\Mutator\MutatorParser;
use Infection\Mutator\NoopMutator;
use Infection\Mutator\Removal\MethodCallRemoval;
use Infection\StaticAnalysis\StaticAnalysisToolTypes;
use Infection\TestFramework\MapSourceClassToTestStrategy;
use Infection\TestFramework\TestFrameworkTypes;
use Infection\Testing\SingletonContainer;
use Infection\Tests\Fixtures\DummyCiDetector;
use Infection\Tests\Fixtures\Mutator\CustomMutator;
use function Infection\Tests\normalizePath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\SplFileInfo;
use function sys_get_temp_dir;

#[Group('integration')]
#[CoversClass(ConfigurationFactory::class)]
final class ConfigurationFactoryTest extends TestCase
{
    use ConfigurationAssertions;

    /**
     * @var array<string, Mutator>|null
     */
    private static $mutators;

    public static function tearDownAfterClass(): void
    {
        self::$mutators = null;
    }

    /**
     * @param SplFileInfo[] $expectedSourceDirectories
     * @param SplFileInfo[] $expectedSourceFilesExcludes
     * @param SplFileInfo[] $expectedSourceFiles
     * @param Mutator[] $expectedMutators
     */
    #[DataProvider('valueProvider')]
    public function test_it_can_create_a_configuration(
        bool $ciDetected = false,
        bool $githubActionsDetected = false,
        SchemaConfiguration $schema = new SchemaConfiguration(
            '/path/to/infection.json',
            null,
            new Source([], []),
            new Logs(
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                false,
                null,
                null,
                null,
            ),
            '',
            new PhpUnit(null, null),
            new PhpStan(null, null),
            null,
            null,
            null,
            [],
            null,
            null,
            null,
            null,
            null,
            null,
        ),
        ?string $inputExistingCoveragePath = null,
        ?string $inputInitialTestsPhpOptions = null,
        bool $skipInitialTests = false,
        string $inputLogVerbosity = LogVerbosity::NONE,
        bool $inputDebug = false,
        bool $inputWithUncovered = false,
        bool $inputNoProgress = false,
        ?bool $inputIgnoreMsiWithNoMutations = false,
        ?float $inputMinMsi = null,
        ?int $inputNumberOfShownMutations = 0,
        ?float $inputMinCoveredMsi = null,
        string $inputMutators = '',
        ?string $inputStaticAnalysisTool = null,
        ?string $inputTestFramework = null,
        ?string $inputTestFrameworkExtraOptions = null,
        ?string $inputStaticAnalysisToolOptions = null,
        string $inputFilter = '',
        int $inputThreadsCount = 1,
        bool $inputDryRun = false,
        ?string $inputGitDiffFilter = 'AM',
        bool $inputIsForGitDiffLines = false,
        string $inputGitDiffBase = 'master',
        ?bool $inputUseGitHubAnnotationsLogger = true,
        ?string $inputGitlabLogFilePath = null,
        ?string $inputHtmlLogFilePath = null,
        bool $inputUseNoopMutators = false,
        int $inputMsiPrecision = 2,
        int $expectedTimeout = 10,
        array $expectedSourceDirectories = [],
        array $expectedSourceFiles = [],
        string $expectedFilter = 'src/a.php,src/b.php',
        array $expectedSourceFilesExcludes = [],
        ?Logs $expectedLogs = null,
        ?string $expectedLogVerbosity = LogVerbosity::NONE,
        ?string $expectedTmpDir = null,
        PhpUnit $expectedPhpUnit = new PhpUnit('/path/to', null),
        PhpStan $expectedPhpStan = new PhpStan('/path/to', null),
        ?array $expectedMutators = null,
        string $expectedTestFramework = TestFrameworkTypes::PHPUNIT,
        ?string $expectedBootstrap = null,
        ?string $expectedInitialTestsPhpOptions = null,
        bool $expectedSkipInitialTests = false,
        string $expectedTestFrameworkExtraOptions = '',
        ?string $expectedStaticAnalysisToolOptions = null,
        ?string $expectedCoveragePath = null,
        bool $expectedSkipCoverage = false,
        bool $expectedDebug = false,
        bool $expectedWithUncovered = false,
        bool $expectedNoProgress = false,
        bool $expectedIgnoreMsiWithNoMutations = false,
        ?float $expectedMinMsi = null,
        ?int $expectedNumberOfShownMutations = 0,
        ?float $expectedMinCoveredMsi = null,
        array $expectedIgnoreSourceCodeMutatorsMap = [],
        bool $inputExecuteOnlyCoveringTestCases = true,
        ?string $mapSourceClassToTest = MapSourceClassToTestStrategy::SIMPLE,
        ?string $loggerProjectRootDirectory = null,
        ?string $expectedStaticAnalysisTool = null,
        ?string $mutantId = null,
    ): void {
        $expectedTmpDir ??= sys_get_temp_dir() . '/infection';
        $expectedCoveragePath ??= sys_get_temp_dir() . '/infection';
        $expectedMutators ??= self::getDefaultMutators();

        if ($expectedLogs === null) {
            $expectedLogs = Logs::createEmpty();
            $expectedLogs->setUseGitHubAnnotationsLogger(true);
        }

        $config = $this
            ->createConfigurationFactory($ciDetected, $githubActionsDetected, $schema)
            ->create(
                $schema,
                $inputExistingCoveragePath,
                $inputInitialTestsPhpOptions,
                $skipInitialTests,
                $inputLogVerbosity,
                $inputDebug,
                $inputWithUncovered,
                $inputNoProgress,
                $inputIgnoreMsiWithNoMutations,
                $inputMinMsi,
                $inputNumberOfShownMutations,
                $inputMinCoveredMsi,
                $inputMsiPrecision,
                $inputMutators,
                $inputTestFramework,
                $inputTestFrameworkExtraOptions,
                $inputStaticAnalysisToolOptions,
                $inputFilter,
                $inputThreadsCount,
                $inputDryRun,
                $inputGitDiffFilter,
                $inputIsForGitDiffLines,
                $inputGitDiffBase,
                $inputUseGitHubAnnotationsLogger,
                $inputGitlabLogFilePath,
                $inputHtmlLogFilePath,
                $inputUseNoopMutators,
                $inputExecuteOnlyCoveringTestCases,
                $mapSourceClassToTest,
                $loggerProjectRootDirectory,
                $inputStaticAnalysisTool,
                $mutantId,
            )
        ;

        $this->assertConfigurationStateIs(
            $config,
            $expectedTimeout,
            $expectedSourceDirectories,
            $expectedSourceFiles,
            $expectedFilter,
            $expectedSourceFilesExcludes,
            $expectedLogs,
            $expectedLogVerbosity,
            normalizePath($expectedTmpDir),
            $expectedPhpUnit,
            $expectedPhpStan,
            $expectedMutators,
            $expectedTestFramework,
            $expectedBootstrap,
            $expectedInitialTestsPhpOptions,
            $expectedTestFrameworkExtraOptions,
            $expectedStaticAnalysisToolOptions,
            normalizePath($expectedCoveragePath),
            $expectedSkipCoverage,
            $expectedSkipInitialTests,
            $expectedDebug,
            $expectedWithUncovered,
            $expectedNoProgress,
            $expectedIgnoreMsiWithNoMutations,
            $expectedMinMsi,
            $expectedNumberOfShownMutations,
            $expectedMinCoveredMsi,
            $inputMsiPrecision,
            $inputThreadsCount,
            $inputDryRun,
            $expectedIgnoreSourceCodeMutatorsMap,
            $inputExecuteOnlyCoveringTestCases,
            $inputIsForGitDiffLines,
            $inputGitDiffBase,
            $mapSourceClassToTest,
            $loggerProjectRootDirectory,
            $expectedStaticAnalysisTool,
            $mutantId,
        );
    }

    public function test_it_throws_exception_when_not_known_static_analysis_tool_used_as_input(): void
    {
        $schema = new SchemaConfiguration(
            '/path/to/infection.json',
            null,
            new Source([], []),
            Logs::createEmpty(),
            '',
            new PhpUnit(null, null),
            new PhpStan(null, null),
            null,
            null,
            null,
            [],
            TestFrameworkTypes::PHPUNIT,
            null,
            null,
            null,
            null,
            null,
            StaticAnalysisToolTypes::PHPSTAN,
            null,
        );

        $this->expectExceptionMessage('Expected one of: "phpstan". Got: "non-supported-static-analysis-tool"');

        $this
            ->createConfigurationFactory(
                false,
                false,
                $schema,
            )
            ->create(
                $schema,
                null,
                null,
                false,
                'none',
                false,
                false,
                false,
                false,
                null,
                0,
                null,
                2,
                '',
                TestFrameworkTypes::PHPUNIT,
                null,
                '',
                0,
                false,
                null,
                false,
                'master',
                false,
                null,
                null,
                false,
                false,
                null,
                null,
                'non-supported-static-analysis-tool',
                null,
            )
        ;
    }

    public static function valueProvider(): iterable
    {
        $expectedLogs = Logs::createEmpty();
        $expectedLogs->setUseGitHubAnnotationsLogger(true);

        yield 'minimal' => [];

        yield 'null html file log path with existing path from config file' => self::createValueForHtmlLogFilePath(
            '/from-config.html',
            null,
            '/from-config.html',
        );

        yield 'absolute html file log path' => self::createValueForHtmlLogFilePath(
            '/path/to/from-config.html',
            null,
            '/path/to/from-config.html',
        );

        yield 'relative html file log path' => self::createValueForHtmlLogFilePath(
            'relative/path/to/from-config.html',
            null,
            '/path/to/relative/path/to/from-config.html',
        );

        yield 'override html file log path from CLI option with existing path from config file' => self::createValueForHtmlLogFilePath(
            '/from-config.html',
            '/from-cli.html',
            '/from-cli.html',
        );

        yield 'set html file log path from CLI option when config file has no setting' => self::createValueForHtmlLogFilePath(
            null,
            '/from-cli.html',
            '/from-cli.html',
        );

        yield 'null html file log path in config and CLI' => self::createValueForHtmlLogFilePath(
            null,
            null,
            null,
        );

        yield 'null timeout' => self::createValueForTimeout(
            null,
            10,
        );

        yield 'config timeout' => self::createValueForTimeout(
            20,
            20,
        );

        yield 'null tmp dir' => self::createValueForTmpDir(
            null,
            sys_get_temp_dir() . '/infection',
        );

        yield 'empty tmp dir' => self::createValueForTmpDir(
            '',
            sys_get_temp_dir() . '/infection',
        );

        yield 'relative tmp dir path' => self::createValueForTmpDir(
            'relative/path/to/tmp',
            '/path/to/relative/path/to/tmp/infection',
        );

        yield 'absolute tmp dir path' => self::createValueForTmpDir(
            '/absolute/path/to/tmp',
            '/absolute/path/to/tmp/infection',
        );

        yield 'no existing base path for code coverage' => self::createValueForCoveragePath(
            null,
            false,
            sys_get_temp_dir() . '/infection',
        );

        yield 'absolute base path for code coverage' => self::createValueForCoveragePath(
            '/path/to/coverage',
            true,
            '/path/to/coverage',
        );

        yield 'relative base path for code coverage' => self::createValueForCoveragePath(
            'relative/path/to/coverage',
            true,
            '/path/to/relative/path/to/coverage',
        );

        yield 'no PHPUnit config dir' => self::createValueForPhpUnitConfigDir(
            'relative/path/to/phpunit/config',
            '/path/to/relative/path/to/phpunit/config',
        );

        yield 'relative PHPUnit config dir' => self::createValueForPhpUnitConfigDir(
            'relative/path/to/phpunit/config',
            '/path/to/relative/path/to/phpunit/config',
        );

        yield 'absolute PHPUnit config dir' => self::createValueForPhpUnitConfigDir(
            '/path/to/phpunit/config',
            '/path/to/phpunit/config',
        );

        yield 'progress in non-CI environment' => self::createValueForNoProgress(
            false,
            false,
            false,
        );

        yield 'progress in CI environment' => self::createValueForNoProgress(
            true,
            false,
            true,
        );

        yield 'no progress in non-CI environment' => self::createValueForNoProgress(
            false,
            true,
            true,
        );

        yield 'no progress in CI environment' => self::createValueForNoProgress(
            true,
            true,
            true,
        );

        yield 'Github Actions annotation disabled, not logged in non-Github Actions environment' => self::createValueForGithubActionsDetected(
            false,
            false,
            false,
        );

        yield 'Github Actions annotation disabled, not logged in Github Actions environment' => self::createValueForGithubActionsDetected(
            false,
            true,
            false,
        );

        yield 'Github Actions annotation not provided, not logged in non-Github Actions environment' => self::createValueForGithubActionsDetected(
            null,
            false,
            false,
        );

        yield 'Github Actions annotation not provided, logged in Github Actions environment' => self::createValueForGithubActionsDetected(
            null,
            true,
            true,
        );

        yield 'Github Actions annotation enabled, logged in non-Github Actions environment' => self::createValueForGithubActionsDetected(
            true,
            false,
            true,
        );

        yield 'Github Actions annotation enabled, logged in Github Actions environment' => self::createValueForGithubActionsDetected(
            true,
            true,
            true,
        );

        yield 'null GitLab file log path with existing path from config file' => self::createValueForGitlabLogger(
            '/from-config.json',
            null,
            '/from-config.json',
        );

        yield 'absolute GitLab file log path' => self::createValueForGitlabLogger(
            '/path/to/from-config.json',
            null,
            '/path/to/from-config.json',
        );

        yield 'relative GitLab file log path' => self::createValueForGitlabLogger(
            'relative/path/to/from-config.json',
            null,
            '/path/to/relative/path/to/from-config.json',
        );

        yield 'override GitLab file log path from CLI option with existing path from config file' => self::createValueForGitlabLogger(
            '/from-config.json',
            '/from-cli.json',
            '/from-cli.json',
        );

        yield 'set GitLab file log path from CLI option when config file has no setting' => self::createValueForGitlabLogger(
            null,
            '/from-cli.json',
            '/from-cli.json',
        );

        yield 'null GitLab file log path in config and CLI' => self::createValueForGitlabLogger(
            null,
            null,
            null,
        );

        yield 'ignoreMsiWithNoMutations not specified in schema and true in input' => self::createValueForIgnoreMsiWithNoMutations(
            null,
            true,
            true,
        );

        yield 'ignoreMsiWithNoMutations not specified in schema and false in input' => self::createValueForIgnoreMsiWithNoMutations(
            null,
            false,
            false,
        );

        yield 'ignoreMsiWithNoMutations true in schema and not specified in input' => self::createValueForIgnoreMsiWithNoMutations(
            true,
            null,
            true,
        );

        yield 'ignoreMsiWithNoMutations false in schema and not specified in input' => self::createValueForIgnoreMsiWithNoMutations(
            false,
            null,
            false,
        );

        yield 'ignoreMsiWithNoMutations true in schema and false in input' => self::createValueForIgnoreMsiWithNoMutations(
            true,
            false,
            false,
        );

        yield 'ignoreMsiWithNoMutations false in schema and true in input' => self::createValueForIgnoreMsiWithNoMutations(
            false,
            true,
            true,
        );

        yield 'minMsi not specified in schema and not specified in input' => self::createValueForMinMsi(
            null,
            null,
            null,
        );

        yield 'minMsi specified in schema and not specified in input' => self::createValueForMinMsi(
            33.3,
            null,
            33.3,
        );

        yield 'minMsi not specified in schema and specified in input' => self::createValueForMinMsi(
            null,
            21.2,
            21.2,
        );

        yield 'minMsi specified in schema and specified in input' => self::createValueForMinMsi(
            33.3,
            21.2,
            21.2,
        );

        yield 'minCoveredMsi not specified in schema and not specified in input' => self::createValueForMinCoveredMsi(
            null,
            null,
            null,
        );

        yield 'minCoveredMsi specified in schema and not specified in input' => self::createValueForMinCoveredMsi(
            33.3,
            null,
            33.3,
        );

        yield 'minCoveredMsi not specified in schema and specified in input' => self::createValueForMinCoveredMsi(
            null,
            21.2,
            21.2,
        );

        yield 'minCoveredMsi specified in schema and specified in input' => self::createValueForMinCoveredMsi(
            33.3,
            21.2,
            21.2,
        );

        yield 'no static analysis tool' => self::createValueForStaticAnalysisTool(
            null,
            null,
            null,
        );

        yield 'static analysis tool from config' => self::createValueForStaticAnalysisTool(
            'phpstan',
            null,
            'phpstan',
        );

        yield 'static analysis tool from input' => self::createValueForStaticAnalysisTool(
            null,
            'phpstan',
            'phpstan',
        );

        yield 'static analysis tool from config & input' => self::createValueForStaticAnalysisTool(
            'phpstan',
            'phpstan',
            'phpstan',
        );

        yield 'no test framework' => self::createValueForTestFramework(
            null,
            null,
            'phpunit',
            '',
        );

        yield 'test framework from config' => self::createValueForTestFramework(
            'phpspec',
            null,
            'phpspec',
            '',
        );

        yield 'test framework from input' => self::createValueForTestFramework(
            null,
            'phpspec',
            'phpspec',
            '',
        );

        yield 'test framework from config & input' => self::createValueForTestFramework(
            'phpunit',
            'phpspec',
            'phpspec',
            '',
        );

        yield 'test no test PHP options' => self::createValueForInitialTestsPhpOptions(
            null,
            null,
            null,
        );

        yield 'test test PHP options from config' => self::createValueForInitialTestsPhpOptions(
            '-d zend_extension=xdebug.so',
            null,
            '-d zend_extension=xdebug.so',
        );

        yield 'test test PHP options from input' => self::createValueForInitialTestsPhpOptions(
            null,
            '-d zend_extension=xdebug.so',
            '-d zend_extension=xdebug.so',
        );

        yield 'test test PHP options from config & input' => self::createValueForInitialTestsPhpOptions(
            '-d zend_extension=another_xdebug.so',
            '-d zend_extension=xdebug.so',
            '-d zend_extension=xdebug.so',
        );

        yield 'test no framework PHP options' => self::createValueForTestFrameworkExtraOptions(
            'phpunit',
            null,
            null,
            '',
        );

        yield 'test framework PHP options from config' => self::createValueForTestFrameworkExtraOptions(
            'phpunit',
            '--debug',
            null,
            '--debug',
        );

        yield 'test framework PHP options from input' => self::createValueForTestFrameworkExtraOptions(
            'phpunit',
            null,
            '--debug',
            '--debug',
        );

        yield 'test framework PHP options from config & input' => self::createValueForTestFrameworkExtraOptions(
            'phpunit',
            '--stop-on-failure',
            '--debug',
            '--debug',
        );

        yield 'test framework PHP options from config with phpspec framework' => self::createValueForTestFrameworkExtraOptions(
            'phpspec',
            '--debug',
            null,
            '--debug',
        );

        yield 'test no static analysis tool options' => self::createValueForStaticAnalysisToolOptions(
            null,
            null,
            null,
        );

        yield 'test static analysis tool options from config' => self::createValueForStaticAnalysisToolOptions(
            '--memory-limit=-1',
            null,
            '--memory-limit=-1',
        );

        yield 'test static analysis tool options from input' => self::createValueForStaticAnalysisToolOptions(
            null,
            '--memory-limit=-1',
            '--memory-limit=-1',
        );

        yield 'test static analysis tool options from config & input' => self::createValueForStaticAnalysisToolOptions(
            '--level=max',
            '--memory-limit=-1',
            '--memory-limit=-1',
        );

        yield 'PHPUnit test framework' => self::createValueForTestFrameworkKey(
            'phpunit',
            '--debug',
            '--debug',
        );

        yield 'phpSpec test framework' => self::createValueForTestFrameworkKey(
            'phpspec',
            '--debug',
            '--debug',
        );

        yield 'codeception test framework' => self::createValueForTestFrameworkKey(
            'codeception',
            '--debug',
            '--debug',
        );

        yield 'no mutator' => self::createValueForMutators(
            [],
            '',
            false,
            self::getDefaultMutators(),
        );

        yield 'mutators from config' => self::createValueForMutators(
            [
                '@default' => false,
                'MethodCallRemoval' => (object) [
                    'ignore' => [
                        'Infection\FileSystem\Finder\SourceFilesFinder::__construct::63',
                    ],
                ],
            ],
            '',
            false,
            [
                'MethodCallRemoval' => new IgnoreMutator(
                    new IgnoreConfig([
                        'Infection\FileSystem\Finder\SourceFilesFinder::__construct::63',
                    ]),
                    new MethodCallRemoval(),
                ),
            ],
        );

        yield 'noop mutators from config' => self::createValueForMutators(
            [
                '@default' => false,
                'MethodCallRemoval' => (object) [
                    'ignore' => [
                        'Infection\FileSystem\Finder\SourceFilesFinder::__construct::63',
                    ],
                ],
            ],
            '',
            true,
            [
                'MethodCallRemoval' => new NoopMutator(new IgnoreMutator(
                    new IgnoreConfig([
                        'Infection\FileSystem\Finder\SourceFilesFinder::__construct::63',
                    ]),
                    new MethodCallRemoval(),
                )),
            ],
        );

        yield 'ignore source code by regex' => self::createValueForIgnoreSourceCodeByRegex(
            [
                '@default' => false,
                'MethodCallRemoval' => (object) [
                    'ignoreSourceCodeByRegex' => ['Assert::.*'],
                ],
            ],
            ['MethodCallRemoval' => ['Assert::.*']],
        );

        yield 'ignore source code by regex with duplicates' => self::createValueForIgnoreSourceCodeByRegex(
            [
                '@default' => false,
                'MethodCallRemoval' => (object) [
                    'ignoreSourceCodeByRegex' => [
                        'Assert::.*',
                        'Assert::.*',
                        'Test::.*',
                        'Test::.*',
                    ],
                ],
            ],
            ['MethodCallRemoval' => ['Assert::.*', 'Test::.*']],
        );

        yield 'mutators from config & input' => self::createValueForMutators(
            [
                '@default' => true,
                'global-ignoreSourceCodeByRegex' => ['Assert::.*'],
                'MethodCallRemoval' => (object) [
                    'ignore' => [
                        'Infection\FileSystem\Finder\SourceFilesFinder::__construct::63',
                    ],
                ],
            ],
            'AssignmentEqual,EqualIdentical',
            false,
            [
                'AssignmentEqual' => new AssignmentEqual(),
                'EqualIdentical' => new EqualIdentical(),
            ],
            [
                'AssignmentEqual' => ['Assert::.*'],
                'EqualIdentical' => ['Assert::.*'],
            ],
        );

        yield 'with source files' => [
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source(['src/'], ['vendor/']),
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                [],
                null,
                null,
                null,
                null,
                null,
                5,
                null,
            ),
            'inputFilter' => 'src/Foo.php, src/Bar.php',
            'inputGitDiffFilter' => null,
            'inputUseGitHubAnnotationsLogger' => false,
            'expectedSourceDirectories' => ['src/'],
            'expectedSourceFiles' => [
                new SplFileInfo('src/Foo.php', 'src/Foo.php', 'src/Foo.php'),
                new SplFileInfo('src/Bar.php', 'src/Bar.php', 'src/Bar.php'),
            ],
            'expectedFilter' => 'src/Foo.php, src/Bar.php',
            'expectedSourceFilesExcludes' => ['vendor/'],
            'expectedLogs' => Logs::createEmpty(),
        ];

        yield 'complete' => [
            'ciDetected' => false,
            'githubActionsDetected' => false,
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                10,
                new Source(['src/'], ['vendor/']),
                new Logs(
                    '/text.log',
                    '/report.html',
                    '/summary.log',
                    '/json.log',
                    '/gitlab.log',
                    '/debug.log',
                    '/mutator.log',
                    true,
                    StrykerConfig::forFullReport('master'),
                    '/summary.json',
                    null,
                ),
                'config/tmp',
                new PhpUnit(
                    'config/phpunit-dir',
                    'config/phpunit',
                ),
                new PhpStan('config/phpstan-dir', 'bin/phpstan'),
                null,
                null,
                null,
                ['@default' => true],
                'phpunit',
                __DIR__ . '/../Fixtures/Files/bootstrap/bootstrap.php',
                '-d zend_extension=wrong_xdebug.so',
                '--debug',
                'max',
                'phpstan',
            ),
            'inputExistingCoveragePath' => 'dist/coverage',
            'inputInitialTestsPhpOptions' => '-d zend_extension=xdebug.so',
            'skipInitialTests' => false,
            'inputLogVerbosity' => 'none',
            'inputDebug' => true,
            'inputWithUncovered' => true,
            'inputNoProgress' => true,
            'inputIgnoreMsiWithNoMutations' => true,
            'inputMinMsi' => 72.3,
            'inputNumberOfShownMutations' => 20,
            'inputMinCoveredMsi' => 81.5,
            'inputMutators' => 'TrueValue',
            'inputStaticAnalysisTool' => StaticAnalysisToolTypes::PHPSTAN,
            'inputTestFramework' => 'phpspec',
            'inputTestFrameworkExtraOptions' => '--stop-on-failure',
            'inputFilter' => 'src/Foo.php, src/Bar.php',
            'inputThreadsCount' => 4,
            'inputDryRun' => true,
            'inputGitDiffFilter' => null,
            'inputIsForGitDiffLines' => false,
            'inputGitDiffBase' => 'master',
            'inputUseGitHubAnnotationsLogger' => false,
            'inputGitlabLogFilePath' => null,
            'inputHtmlLogFilePath' => null,
            'inputUseNoopMutators' => false,
            'inputMsiPrecision' => 2,
            'expectedTimeout' => 10,
            'expectedSourceDirectories' => ['src/'],
            'expectedSourceFiles' => [
                new SplFileInfo('src/Foo.php', 'src/Foo.php', 'src/Foo.php'),
                new SplFileInfo('src/Bar.php', 'src/Bar.php', 'src/Bar.php'),
            ],
            'expectedFilter' => 'src/Foo.php, src/Bar.php',
            'expectedSourceFilesExcludes' => ['vendor/'],
            'expectedLogs' => new Logs(
                '/text.log',
                '/report.html',
                '/summary.log',
                '/json.log',
                '/gitlab.log',
                '/debug.log',
                '/mutator.log',
                true,
                StrykerConfig::forFullReport('master'),
                '/summary.json',
            ),
            'expectedLogVerbosity' => 'none',
            'expectedTmpDir' => '/path/to/config/tmp/infection',
            'expectedPhpUnit' => new PhpUnit(
                '/path/to/config/phpunit-dir',
                'config/phpunit',
            ),
            'expectedPhpStan' => new PhpStan('/path/to/config/phpstan-dir', 'bin/phpstan'),
            'expectedMutators' => (static fn (): array => [
                'TrueValue' => new TrueValue(new TrueValueConfig([])),
            ])(),
            'expectedTestFramework' => 'phpspec',
            'expectedBootstrap' => __DIR__ . '/../Fixtures/Files/bootstrap/bootstrap.php',
            'expectedInitialTestsPhpOptions' => '-d zend_extension=xdebug.so',
            'expectedSkipInitialTests' => false,
            'expectedTestFrameworkExtraOptions' => '--stop-on-failure',
            'expectedStaticAnalysisToolOptions' => null,
            'expectedCoveragePath' => '/path/to/dist/coverage',
            'expectedSkipCoverage' => true,
            'expectedDebug' => true,
            'expectedWithUncovered' => true,
            'expectedNoProgress' => true,
            'expectedIgnoreMsiWithNoMutations' => true,
            'expectedMinMsi' => 72.3,
            'expectedNumberOfShownMutations' => 20,
            'expectedMinCoveredMsi' => 81.5,
            'expectedIgnoreSourceCodeMutatorsMap' => [],
            'inputExecuteOnlyCoveringTestCases' => false,
            'mapSourceClassToTest' => MapSourceClassToTestStrategy::SIMPLE,
            'loggerProjectRootDirectory' => null,
            'expectedStaticAnalysisTool' => StaticAnalysisToolTypes::PHPSTAN,
            'mutantId' => 'h4sh', // $mutantId
        ];

        yield 'custom mutator with bootstrap file' => [
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                new Logs(
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    false,
                    null,
                    null,
                    null,
                ),
                '',
                new PhpUnit(null, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                ['@default' => false, 'CustomMutator' => true],
                null,
                __DIR__ . '/../Fixtures/Files/bootstrap/bootstrap.php',
                null,
                null,
                null,
                null,
            ),
            'expectedMutators' => (static fn (): array => [
                'CustomMutator' => new CustomMutator(),
            ])(),
            'expectedBootstrap' => __DIR__ . '/../Fixtures/Files/bootstrap/bootstrap.php',
        ];
    }

    private static function createValueForTimeout(
        ?int $schemaTimeout,
        int $expectedTimeout,
    ): array {
        return [
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                $schemaTimeout,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                [],
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ),
            'expectedTimeout' => $expectedTimeout,
        ];
    }

    private static function createValueForTmpDir(
        ?string $configTmpDir,
        ?string $expectedTmpDir,
    ): array {
        return [
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                $configTmpDir,
                new PhpUnit(null, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                [],
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ),
            'expectedTmpDir' => $expectedTmpDir,
            'expectedCoveragePath' => $expectedTmpDir,
        ];
    }

    private static function createValueForCoveragePath(
        ?string $existingCoveragePath,
        bool $expectedSkipCoverage,
        string $expectedCoveragePath,
    ): array {
        return [
            'inputExistingCoveragePath' => $existingCoveragePath,
            'expectedCoveragePath' => $expectedCoveragePath,
            'expectedSkipCoverage' => $expectedSkipCoverage,
        ];
    }

    private static function createValueForPhpUnitConfigDir(
        ?string $phpUnitConfigDir,
        ?string $expectedPhpUnitConfigDir,
    ): array {
        return [
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit($phpUnitConfigDir, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                [],
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ),
            'expectedPhpUnit' => new PhpUnit($expectedPhpUnitConfigDir, null),
        ];
    }

    private static function createValueForNoProgress(
        bool $ciDetected,
        bool $noProgress,
        bool $expectedNoProgress,
    ): array {
        return [
            'ciDetected' => $ciDetected,
            'inputNoProgress' => $noProgress,
            'expectedNoProgress' => $expectedNoProgress,
        ];
    }

    private static function createValueForGithubActionsDetected(
        ?bool $inputUseGitHubAnnotationsLogger,
        bool $githubActionsDetected,
        bool $useGitHubAnnotationsLogger,
    ): array {
        $expectedLogs = new Logs(
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            $useGitHubAnnotationsLogger,
            null,
            null,
            null,
        );

        return [
            'githubActionsDetected' => $githubActionsDetected,
            'inputUseGitHubAnnotationsLogger' => $inputUseGitHubAnnotationsLogger,
            'expectedLogs' => $expectedLogs,
        ];
    }

    private static function createValueForGitlabLogger(
        ?string $gitlabFileLogPathInConfig,
        ?string $gitlabFileLogPathFromCliOption,
        ?string $expectedGitlabFileLogPath,
    ): array {
        $expectedLogs = new Logs(
            null,
            null,
            null,
            null,
            $expectedGitlabFileLogPath,
            null,
            null,
            true,
            null,
            null,
        );

        return [
            'inputGitlabLogFilePath' => $gitlabFileLogPathFromCliOption,
            'expectedLogs' => $expectedLogs,
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                new Logs(
                    null,
                    null,
                    null,
                    null,
                    $gitlabFileLogPathInConfig,
                    null,
                    null,
                    false,
                    null,
                    null,
                    null,
                ),
                '',
                new PhpUnit(null, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                [],
                null,
                null,
                null,
                null,
                null,
                null,
            ),
        ];
    }

    private static function createValueForIgnoreMsiWithNoMutations(
        ?bool $ignoreMsiWithNoMutationsFromSchemaConfiguration,
        ?bool $ignoreMsiWithNoMutationsFromInput,
        ?bool $expectedIgnoreMsiWithNoMutations,
    ): array {
        return [
            'inputIgnoreMsiWithNoMutations' => $ignoreMsiWithNoMutationsFromInput,
            'expectedIgnoreMsiWithNoMutations' => $expectedIgnoreMsiWithNoMutations,
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit('/path/to', null),
                new PhpStan('/path/to', null),
                $ignoreMsiWithNoMutationsFromSchemaConfiguration,
                null,
                null,
                [],
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ),
        ];
    }

    private static function createValueForMinMsi(
        ?float $minMsiFromSchemaConfiguration,
        ?float $minMsiFromInput,
        ?float $expectedMinMsi,
    ): array {
        return [
            'inputMinMsi' => $minMsiFromInput,
            'expectedMinMsi' => $expectedMinMsi,
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit('/path/to', null),
                new PhpStan('/path/to', null),
                null,
                $minMsiFromSchemaConfiguration,
                null,
                [],
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ),
        ];
    }

    private static function createValueForMinCoveredMsi(
        ?float $minCoveredMsiFromSchemaConfiguration,
        ?float $minCoveredMsiFromInput,
        ?float $expectedMinCoveredMsi,
    ): array {
        return [
            'inputMinCoveredMsi' => $minCoveredMsiFromInput,
            'expectedMinCoveredMsi' => $expectedMinCoveredMsi,
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit('/path/to', null),
                new PhpStan('/path/to', null),
                null,
                null,
                $minCoveredMsiFromSchemaConfiguration,
                [],
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ),
        ];
    }

    private static function createValueForTestFramework(
        ?string $configTestFramework,
        ?string $inputTestFramework,
        string $expectedTestFramework,
        string $expectedTestFrameworkExtraOptions,
    ): array {
        return [
            'inputTestFramework' => $inputTestFramework,
            'expectedTestFramework' => $expectedTestFramework,
            'expectedTestFrameworkExtraOptions' => $expectedTestFrameworkExtraOptions,
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                [],
                $configTestFramework,
                null,
                null,
                null,
                null,
                null,
                null,
            ),
        ];
    }

    private static function createValueForStaticAnalysisTool(
        ?string $configStaticAnalysisTool,
        ?string $inputStaticAnalysisTool,
        ?string $expectedStaticAnalysisTool,
    ): array {
        return [
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                [],
                TestFrameworkTypes::PHPUNIT,
                null,
                null,
                null,
                null,
                null,
                $configStaticAnalysisTool,
            ),
            'inputStaticAnalysisTool' => $inputStaticAnalysisTool,
            'expectedStaticAnalysisTool' => $expectedStaticAnalysisTool,
        ];
    }

    private static function createValueForInitialTestsPhpOptions(
        ?string $configInitialTestsPhpOptions,
        ?string $inputInitialTestsPhpOptions,
        ?string $expectedInitialTestPhpOptions,
    ): array {
        return [
            'inputInitialTestsPhpOptions' => $inputInitialTestsPhpOptions,
            'expectedInitialTestsPhpOptions' => $expectedInitialTestPhpOptions,
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                [],
                null,
                null,
                $configInitialTestsPhpOptions,
                null,
                null,
                null,
                null,
            ),
        ];
    }

    private static function createValueForTestFrameworkExtraOptions(
        string $configTestFramework,
        ?string $configTestFrameworkExtraOptions,
        ?string $inputTestFrameworkExtraOptions,
        string $expectedTestFrameworkExtraOptions,
    ): array {
        return [
            'inputTestFrameworkExtraOptions' => $inputTestFrameworkExtraOptions,
            'expectedTestFramework' => $configTestFramework,
            'expectedTestFrameworkExtraOptions' => $expectedTestFrameworkExtraOptions,
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                [],
                $configTestFramework,
                null,
                null,
                $configTestFrameworkExtraOptions,
                null,
                null,
                null,
            ),
        ];
    }

    private static function createValueForStaticAnalysisToolOptions(
        ?string $configStaticAnalysisToolOptions,
        ?string $inputStaticAnalysisToolOptions,
        ?string $expectedStaticAnalysisToolOptions,
    ): array {
        return [
            'inputStaticAnalysisToolOptions' => $inputStaticAnalysisToolOptions,
            'expectedStaticAnalysisToolOptions' => $expectedStaticAnalysisToolOptions,
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                [],
                null,
                null,
                null,
                null,
                $configStaticAnalysisToolOptions,
                null,
                null,
            ),
        ];
    }

    private static function createValueForTestFrameworkKey(
        string $configTestFramework,
        string $inputTestFrameworkExtraOptions,
        string $expectedTestFrameworkExtraOptions,
    ): array {
        return [
            'inputTestFrameworkExtraOptions' => $inputTestFrameworkExtraOptions,
            'expectedTestFramework' => $configTestFramework,
            'expectedTestFrameworkExtraOptions' => $expectedTestFrameworkExtraOptions,
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                '',
                new PhpUnit(null, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                [],
                $configTestFramework,
                null,
                null,
                null,
                null,
                null,
                null,
            ),
        ];
    }

    /**
     * @param array<string, Mutator> $expectedMutators
     * @param array<string, array<int, string>> $expectedIgnoreSourceCodeMutatorsMap
     */
    private static function createValueForMutators(
        array $configMutators,
        string $inputMutators,
        bool $useNoopMutators,
        array $expectedMutators,
        array $expectedIgnoreSourceCodeMutatorsMap = [],
    ): array {
        return [
            'inputMutators' => $inputMutators,
            'inputUseNoopMutators' => $useNoopMutators,
            'expectedMutators' => $expectedMutators,
            'expectedIgnoreSourceCodeMutatorsMap' => $expectedIgnoreSourceCodeMutatorsMap,
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                null,
                new PhpUnit(null, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                $configMutators,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ),
        ];
    }

    /**
     * @param array<string, mixed> $configMutators
     * @param array<string, array<int, string>> $expectedIgnoreSourceCodeMutatorsMap
     */
    private static function createValueForIgnoreSourceCodeByRegex(
        array $configMutators,
        array $expectedIgnoreSourceCodeMutatorsMap,
    ): array {
        return [
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                Logs::createEmpty(),
                null,
                new PhpUnit(null, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                $configMutators,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
            ),
            'expectedIgnoreSourceCodeMutatorsMap' => $expectedIgnoreSourceCodeMutatorsMap,
            'expectedMutators' => [
                'MethodCallRemoval' => new MethodCallRemoval(),
            ],
        ];
    }

    private static function createValueForHtmlLogFilePath(?string $htmlFileLogPathInConfig, ?string $htmlFileLogPathFromCliOption, ?string $expectedHtmlFileLogPath): array
    {
        $expectedLogs = new Logs(
            null,
            $expectedHtmlFileLogPath,
            null,
            null,
            null,
            null,
            null,
            true,
            null,
            null,
            null,
        );

        return [
            'inputHtmlLogFilePath' => $htmlFileLogPathFromCliOption,
            'expectedLogs' => $expectedLogs,
            'schema' => new SchemaConfiguration(
                '/path/to/infection.json',
                null,
                new Source([], []),
                new Logs(
                    null,
                    $htmlFileLogPathInConfig,
                    null,
                    null,
                    null,
                    null,
                    null,
                    false,
                    null,
                    null,
                    null,
                ),
                '',
                new PhpUnit(null, null),
                new PhpStan(null, null),
                null,
                null,
                null,
                [],
                null,
                null,
                null,
                null,
                null,
                null,
            ),
        ];
    }

    /**
     * @return array<string, Mutator>
     */
    private static function getDefaultMutators(): array
    {
        if (self::$mutators === null) {
            self::$mutators = SingletonContainer::getContainer()
                ->getMutatorFactory()
                ->create(
                    SingletonContainer::getContainer()
                        ->getMutatorResolver()
                        ->resolve(['@default' => true]),
                    false,
                )
            ;
        }

        return self::$mutators;
    }

    private function createConfigurationFactory(
        bool $ciDetected,
        bool $githubActionsDetected,
        SchemaConfiguration $schema,
    ): ConfigurationFactory {
        /** @var SourceFileCollector&MockObject $sourceFilesCollector */
        $sourceFilesCollector = $this->createMock(SourceFileCollector::class);

        $sourceFilesCollector->expects($this->once())
            ->method('collectFiles')
            ->with($schema->getSource()->getDirectories(), $schema->getSource()->getExcludes())
            ->willReturnCallback(
                static function (array $source, array $excludes) {
                    if ($source === ['src/'] && $excludes === ['vendor/']) {
                        return [
                            new SplFileInfo('src/Foo.php', 'src/Foo.php', 'src/Foo.php'),
                            new SplFileInfo('src/Bar.php', 'src/Bar.php', 'src/Bar.php'),
                        ];
                    }

                    return [];
                },
            );

        $gitDiffFilesProviderMock = $this->createMock(GitDiffFileProvider::class);
        $gitDiffFilesProviderMock->method('provide')->willReturn('src/a.php,src/b.php');

        return new ConfigurationFactory(
            new TmpDirProvider(),
            SingletonContainer::getContainer()->getMutatorResolver(),
            SingletonContainer::getContainer()->getMutatorFactory(),
            new MutatorParser(),
            $sourceFilesCollector,
            new DummyCiDetector($ciDetected, $githubActionsDetected),
            $gitDiffFilesProviderMock,
        );
    }
}
