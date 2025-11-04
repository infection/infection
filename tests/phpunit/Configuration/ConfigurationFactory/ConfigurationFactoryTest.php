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

namespace Infection\Tests\Configuration\ConfigurationFactory;

use Infection\Configuration\Configuration;
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
use Infection\Tests\Configuration\ConfigurationBuilder;
use Infection\Tests\Configuration\Entry\LogsBuilder;
use Infection\Tests\Configuration\Schema\SchemaConfigurationBuilder;
use Infection\Tests\Fixtures\DummyCiDetector;
use Infection\Tests\Fixtures\Mutator\CustomMutator;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Symfony\Component\Finder\SplFileInfo;
use function sys_get_temp_dir;
use function var_export;

#[Group('integration')]
#[CoversClass(ConfigurationFactory::class)]
final class ConfigurationFactoryTest extends TestCase
{
    /**
     * @var array<string, Mutator>|null
     */
    private static $mutators;

    public static function tearDownAfterClass(): void
    {
        self::$mutators = null;
    }

    #[DataProvider('valueProvider')]
    public function test_it_can_create_a_configuration(
        ConfigurationFactoryScenario $scenario,
    ): void {
        $schema = $scenario->schemaBuilder->build();

        $actual = $this
            ->createConfigurationFactory(
                $scenario->ciDetected,
                $scenario->githubActionsDetected,
                $schema,
            )
            ->create(...$scenario->inputBuilder->build($schema))
        ;

        $this->assertEquals(
            $scenario->expected,
            $actual,
        );
    }

    public function test_it_throws_exception_when_not_known_static_analysis_tool_used_as_input(): void
    {
        $schema = new SchemaConfiguration(
            file: '/path/to/infection.json',
            timeout: null,
            source: new Source([], []),
            logs: Logs::createEmpty(),
            tmpDir: '',
            phpUnit: new PhpUnit(null, null),
            phpStan: new PhpStan(null, null),
            ignoreMsiWithNoMutations: null,
            minMsi: null,
            minCoveredMsi: null,
            mutators: [],
            testFramework: TestFrameworkTypes::PHPUNIT,
            bootstrap: null,
            initialTestsPhpOptions: null,
            testFrameworkExtraOptions: null,
            staticAnalysisToolOptions: null,
            threads: null,
            staticAnalysisTool: StaticAnalysisToolTypes::PHPSTAN,
        );

        $this->expectExceptionMessage('Expected one of: "phpstan". Got: "non-supported-static-analysis-tool"');

        $this
            ->createConfigurationFactory(
                ciDetected: false,
                githubActionsDetected: false,
                schema: $schema,
            )
            ->create(
                schema: $schema,
                existingCoveragePath: null,
                initialTestsPhpOptions: null,
                skipInitialTests: false,
                logVerbosity: 'none',
                debug: false,
                withUncovered: false,
                noProgress: false,
                ignoreMsiWithNoMutations: false,
                minMsi: null,
                numberOfShownMutations: 0,
                minCoveredMsi: null,
                msiPrecision: 2,
                mutatorsInput: '',
                testFramework: TestFrameworkTypes::PHPUNIT,
                testFrameworkExtraOptions: null,
                staticAnalysisToolOptions: null,
                filter: '',
                threadCount: 0,
                dryRun: false,
                gitDiffFilter: null,
                isForGitDiffLines: false,
                gitDiffBase: 'master',
                useGitHubLogger: false,
                gitlabLogFilePath: null,
                htmlLogFilePath: null,
                textLogFilePath: null,
                useNoopMutators: false,
                executeOnlyCoveringTestCases: false,
                mapSourceClassToTestStrategy: null,
                loggerProjectRootDirectory: null,
                staticAnalysisTool: 'non-supported-static-analysis-tool',
                mutantId: null,
            )
        ;
    }

    public static function valueProvider(): iterable
    {
        $defaultLogsBuilder = LogsBuilder::withMinimalTestData()
            ->withUseGitHubAnnotationsLogger(true);
        $defaultLogs = $defaultLogsBuilder->build();

        $defaultSchema = new SchemaConfiguration(
            file: '/path/to/infection.json',
            timeout: null,
            source: new Source([], []),
            logs: Logs::createEmpty(),
            tmpDir: '',
            phpUnit: new PhpUnit(null, null),
            phpStan: new PhpStan(null, null),
            ignoreMsiWithNoMutations: null,
            minMsi: null,
            minCoveredMsi: null,
            mutators: [],
            testFramework: null,
            bootstrap: null,
            initialTestsPhpOptions: null,
            testFrameworkExtraOptions: null,
            staticAnalysisToolOptions: null,
            threads: null,
            staticAnalysisTool: null,
        );
        $defaultSchemaBuilder = SchemaConfigurationBuilder::from($defaultSchema);

        $defaultInputBuilder = new ConfigurationFactoryInputBuilder(
            existingCoveragePath: null,
            initialTestsPhpOptions: null,
            skipInitialTests: false,
            logVerbosity: LogVerbosity::NONE,
            debug: false,
            withUncovered: false,
            noProgress: false,
            ignoreMsiWithNoMutations: false,
            minMsi: null,
            numberOfShownMutations: 0,
            minCoveredMsi: null,
            msiPrecision: 2,
            mutatorsInput: '',
            testFramework: null,
            testFrameworkExtraOptions: null,
            staticAnalysisToolOptions: null,
            filter: '',
            threadCount: 1,
            dryRun: false,
            gitDiffFilter: 'AM',
            isForGitDiffLines: false,
            gitDiffBase: 'master',
            useGitHubLogger: true,
            gitlabLogFilePath: null,
            htmlLogFilePath: null,
            textLogFilePath: null,
            useNoopMutators: false,
            executeOnlyCoveringTestCases: true,
            mapSourceClassToTestStrategy: MapSourceClassToTestStrategy::SIMPLE,
            loggerProjectRootDirectory: null,
            staticAnalysisTool: null,
            mutantId: null,
        );

        $defaultConfiguration = new Configuration(
            timeout: 10,
            sourceDirectories: [],
            sourceFiles: [],
            sourceFilesFilter: 'src/a.php,src/b.php',
            sourceFilesExcludes: [],
            logs: $defaultLogs,
            logVerbosity: LogVerbosity::NONE,
            tmpDir: sys_get_temp_dir() . '/infection',
            phpUnit: new PhpUnit('/path/to', null),
            phpStan: new PhpStan('/path/to', null),
            mutators: self::getDefaultMutators(),
            testFramework: TestFrameworkTypes::PHPUNIT,
            bootstrap: null,
            initialTestsPhpOptions: null,
            testFrameworkExtraOptions: '',
            staticAnalysisToolOptions: null,
            coveragePath: sys_get_temp_dir() . '/infection',
            skipCoverage: false,
            skipInitialTests: false,
            debug: false,
            withUncovered: false,
            noProgress: false,
            ignoreMsiWithNoMutations: false,
            minMsi: null,
            numberOfShownMutations: 0,
            minCoveredMsi: null,
            msiPrecision: 2,
            threadCount: 1,
            dryRun: false,
            ignoreSourceCodeMutatorsMap: [],
            executeOnlyCoveringTestCases: true,
            isForGitDiffLines: false,
            gitDiffBase: 'master',
            mapSourceClassToTestStrategy: MapSourceClassToTestStrategy::SIMPLE,
            loggerProjectRootDirectory: null,
            staticAnalysisTool: null,
            mutantId: null,
        );
        $defaultConfigurationBuilder = ConfigurationBuilder::from($defaultConfiguration);

        $defaultScenario = ConfigurationFactoryScenario::create(
            ciDetected: false,
            githubActionsDetected: false,
            schemaBuilder: $defaultSchemaBuilder,
            inputBuilder: $defaultInputBuilder,
            expected: $defaultConfiguration,
        );

        yield 'minimal' => [$defaultScenario];

        yield 'null html file log path with existing path from config file' => [
            $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                ->withLogs(
                    $defaultLogsBuilder
                    ->withHtmlLogFilePath('/from-config.html')
                    ->build(),
                )
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withLogs(
                        $defaultLogsBuilder
                            ->withHtmlLogFilePath('/from-config.html')
                            ->build(),
                    )
                ->build(),
            ),
        ];

        yield 'absolute html file log path' => [
            self::createValueForHtmlLogFilePath(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                '/path/to/from-config.html',
                null,
                '/path/to/from-config.html',
            ),
        ];

        yield 'relative html file log path' => [
            self::createValueForHtmlLogFilePath(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                'relative/path/to/from-config.html',
                null,
                '/path/to/relative/path/to/from-config.html',
            ),
        ];

        yield 'override html file log path from CLI option with existing path from config file' => [
            self::createValueForHtmlLogFilePath(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                '/from-config.html',
                '/from-cli.html',
                '/from-cli.html',
            ),
        ];

        yield 'set html file log path from CLI option when config file has no setting' => [
            self::createValueForHtmlLogFilePath(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                null,
                '/from-cli.html',
                '/from-cli.html',
            ),
        ];

        yield 'null html file log path in config and CLI' => [
            self::createValueForHtmlLogFilePath(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                null,
                null,
                null,
            ),
        ];

        yield 'null text file log path with existing path from config file' => [
            $defaultScenario
                ->forValueForTextLogFilePath(
                    '/from-config.text',
                    null,
                    '/from-config.text',
                ),
        ];

        yield 'absolute text file log path' => [
            $defaultScenario
                ->forValueForTextLogFilePath(
                    '/path/to/from-config.text',
                    null,
                    '/path/to/from-config.text',
                ),
        ];

        yield 'relative text file log path' => [
            $defaultScenario
                ->forValueForTextLogFilePath(
                    'relative/path/to/from-config.text',
                    null,
                    '/path/to/relative/path/to/from-config.text',
                ),
        ];

        yield 'override text file log path from CLI option with existing path from config file' => [
            $defaultScenario
                ->forValueForTextLogFilePath(
                    '/from-config.text',
                    '/from-cli.text',
                    '/from-cli.text',
                ),
        ];

        yield 'set text file log path from CLI option when config file has no setting' => [
            $defaultScenario
                ->forValueForTextLogFilePath(
                    null,
                    '/from-cli.text',
                    '/from-cli.text',
                ),
        ];

        yield 'null text file log path in config and CLI' => [
            $defaultScenario
                ->forValueForTextLogFilePath(
                    null,
                    null,
                    null,
                ),
        ];

        yield 'null timeout' => [
            self::createValueForTimeout(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                10,
            ),
        ];

        yield 'config timeout' => [
            self::createValueForTimeout(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                20,
                20,
            ),
        ];

        yield 'null tmp dir' => [
            self::createValueForTmpDir(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                sys_get_temp_dir() . '/infection',
            ),
        ];

        yield 'empty tmp dir' => [
            self::createValueForTmpDir(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                '',
                sys_get_temp_dir() . '/infection',
            ),
        ];

        yield 'relative tmp dir path' => [
            self::createValueForTmpDir(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'relative/path/to/tmp',
                '/path/to/relative/path/to/tmp/infection',
            ),
        ];

        yield 'absolute tmp dir path' => [
            self::createValueForTmpDir(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                '/absolute/path/to/tmp',
                '/absolute/path/to/tmp/infection',
            ),
        ];

        yield 'no existing base path for code coverage' => [
            self::createValueForCoveragePath(
                $defaultScenario,
                $defaultConfigurationBuilder,
                null,
                false,
                sys_get_temp_dir() . '/infection',
            ),
        ];

        yield 'absolute base path for code coverage' => [
            self::createValueForCoveragePath(
                $defaultScenario,
                $defaultConfigurationBuilder,
                '/path/to/coverage',
                true,
                '/path/to/coverage',
            ),
        ];

        yield 'relative base path for code coverage' => [
            self::createValueForCoveragePath(
                $defaultScenario,
                $defaultConfigurationBuilder,
                'relative/path/to/coverage',
                true,
                '/path/to/relative/path/to/coverage',
            ),
        ];

        yield 'no PHPUnit config dir' => [
            self::createValueForPhpUnitConfigDir(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'relative/path/to/phpunit/config',
                '/path/to/relative/path/to/phpunit/config',
            ),
        ];

        yield 'relative PHPUnit config dir' => [
            self::createValueForPhpUnitConfigDir(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'relative/path/to/phpunit/config',
                '/path/to/relative/path/to/phpunit/config',
            ),
        ];

        yield 'absolute PHPUnit config dir' => [
            self::createValueForPhpUnitConfigDir(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                '/path/to/phpunit/config',
                '/path/to/phpunit/config',
            ),
        ];

        yield 'progress in non-CI environment' => [
            self::createValueForNoProgress(
                $defaultScenario,
                $defaultConfigurationBuilder,
                false,
                false,
                false,
            ),
        ];

        yield 'progress in CI environment' => [
            self::createValueForNoProgress(
                $defaultScenario,
                $defaultConfigurationBuilder,
                true,
                false,
                true,
            ),
        ];

        yield 'no progress in non-CI environment' => [
            self::createValueForNoProgress(
                $defaultScenario,
                $defaultConfigurationBuilder,
                false,
                true,
                true,
            ),
        ];

        yield 'no progress in CI environment' => [
            self::createValueForNoProgress(
                $defaultScenario,
                $defaultConfigurationBuilder,
                true,
                true,
                true,
            ),
        ];

        yield 'Github Actions annotation disabled, not logged in non-Github Actions environment' => [
            self::createValueForGithubActionsDetected(
                $defaultScenario,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                false,
                false,
                false,
            ),
        ];

        yield 'Github Actions annotation disabled, not logged in Github Actions environment' => [
            self::createValueForGithubActionsDetected(
                $defaultScenario,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                false,
                true,
                false,
            ),
        ];

        yield 'Github Actions annotation not provided, not logged in non-Github Actions environment' => [
            self::createValueForGithubActionsDetected(
                $defaultScenario,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                null,
                false,
                false,
            ),
        ];

        yield 'Github Actions annotation not provided, logged in Github Actions environment' => [
            self::createValueForGithubActionsDetected(
                $defaultScenario,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                null,
                true,
                true,
            ),
        ];

        yield 'Github Actions annotation enabled, logged in non-Github Actions environment' => [
            self::createValueForGithubActionsDetected(
                $defaultScenario,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                true,
                false,
                true,
            ),
        ];

        yield 'Github Actions annotation enabled, logged in Github Actions environment' => [
            self::createValueForGithubActionsDetected(
                $defaultScenario,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                true,
                true,
                true,
            ),
        ];

        yield 'null GitLab file log path with existing path from config file' => [
            self::createValueForGitlabLogger(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                '/from-config.json',
                null,
                '/from-config.json',
            ),
        ];

        yield 'absolute GitLab file log path' => [
            self::createValueForGitlabLogger(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                '/path/to/from-config.json',
                null,
                '/path/to/from-config.json',
            ),
        ];

        yield 'relative GitLab file log path' => [
            self::createValueForGitlabLogger(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                'relative/path/to/from-config.json',
                null,
                '/path/to/relative/path/to/from-config.json',
            ),
        ];

        yield 'override GitLab file log path from CLI option with existing path from config file' => [
            self::createValueForGitlabLogger(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                '/from-config.json',
                '/from-cli.json',
                '/from-cli.json',
            ),
        ];

        yield 'set GitLab file log path from CLI option when config file has no setting' => [
            self::createValueForGitlabLogger(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                null,
                '/from-cli.json',
                '/from-cli.json',
            ),
        ];

        yield 'null GitLab file log path in config and CLI' => [
            self::createValueForGitlabLogger(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultLogsBuilder,
                $defaultConfigurationBuilder,
                null,
                null,
                null,
            ),
        ];

        yield 'ignoreMsiWithNoMutations not specified in schema and true in input' => [
            self::createValueForIgnoreMsiWithNoMutations(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                true,
                true,
            ),
        ];

        yield 'ignoreMsiWithNoMutations not specified in schema and false in input' => [
            self::createValueForIgnoreMsiWithNoMutations(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                false,
                false,
            ),
        ];

        yield 'ignoreMsiWithNoMutations true in schema and not specified in input' => [
            self::createValueForIgnoreMsiWithNoMutations(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                true,
                null,
                true,
            ),
        ];

        yield 'ignoreMsiWithNoMutations false in schema and not specified in input' => [
            self::createValueForIgnoreMsiWithNoMutations(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                false,
                null,
                false,
            ),
        ];

        yield 'ignoreMsiWithNoMutations true in schema and false in input' => [
            self::createValueForIgnoreMsiWithNoMutations(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                true,
                false,
                false,
            ),
        ];

        yield 'ignoreMsiWithNoMutations false in schema and true in input' => [
            self::createValueForIgnoreMsiWithNoMutations(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                false,
                true,
                true,
            ),
        ];

        yield 'minMsi not specified in schema and not specified in input' => [
            self::createValueForMinMsi(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                null,
                null,
            ),
        ];

        yield 'minMsi specified in schema and not specified in input' => [
            self::createValueForMinMsi(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                33.3,
                null,
                33.3,
            ),
        ];

        yield 'minMsi not specified in schema and specified in input' => [
            self::createValueForMinMsi(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                21.2,
                21.2,
            ),
        ];

        yield 'minMsi specified in schema and specified in input' => [
            self::createValueForMinMsi(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                33.3,
                21.2,
                21.2,
            ),
        ];

        yield 'minCoveredMsi not specified in schema and not specified in input' => [
            self::createValueForMinCoveredMsi(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                null,
                null,
            ),
        ];

        yield 'minCoveredMsi specified in schema and not specified in input' => [
            self::createValueForMinCoveredMsi(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                33.3,
                null,
                33.3,
            ),
        ];

        yield 'minCoveredMsi not specified in schema and specified in input' => [
            self::createValueForMinCoveredMsi(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                21.2,
                21.2,
            ),
        ];

        yield 'minCoveredMsi specified in schema and specified in input' => [
            self::createValueForMinCoveredMsi(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                33.3,
                21.2,
                21.2,
            ),
        ];

        yield 'no static analysis tool' => [
            self::createValueForStaticAnalysisTool(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                null,
                null,
            ),
        ];

        yield 'static analysis tool from config' => [
            self::createValueForStaticAnalysisTool(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'phpstan',
                null,
                'phpstan',
            ),
        ];

        yield 'static analysis tool from input' => [
            self::createValueForStaticAnalysisTool(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                'phpstan',
                'phpstan',
            ),
        ];

        yield 'static analysis tool from config & input' => [
            self::createValueForStaticAnalysisTool(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'phpstan',
                'phpstan',
                'phpstan',
            ),
        ];

        yield 'no test framework' => [
            self::createValueForTestFramework(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                null,
                'phpunit',
                '',
            ),
        ];

        yield 'test framework from config' => [
            self::createValueForTestFramework(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'phpspec',
                null,
                'phpspec',
                '',
            ),
        ];

        yield 'test framework from input' => [
            self::createValueForTestFramework(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                'phpspec',
                'phpspec',
                '',
            ),
        ];

        yield 'test framework from config & input' => [
            self::createValueForTestFramework(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'phpunit',
                'phpspec',
                'phpspec',
                '',
            ),
        ];

        yield 'test no test PHP options' => [
            self::createValueForInitialTestsPhpOptions(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                null,
                null,
            ),
        ];

        yield 'test test PHP options from config' => [
            self::createValueForInitialTestsPhpOptions(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                '-d zend_extension=xdebug.so',
                null,
                '-d zend_extension=xdebug.so',
            ),
        ];

        yield 'test test PHP options from input' => [
            self::createValueForInitialTestsPhpOptions(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                '-d zend_extension=xdebug.so',
                '-d zend_extension=xdebug.so',
            ),
        ];

        yield 'test test PHP options from config & input' => [
            self::createValueForInitialTestsPhpOptions(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                '-d zend_extension=another_xdebug.so',
                '-d zend_extension=xdebug.so',
                '-d zend_extension=xdebug.so',
            ),
        ];

        yield 'test no framework PHP options' => [
            self::createValueForTestFrameworkExtraOptions(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'phpunit',
                null,
                null,
                '',
            ),
        ];

        yield 'test framework PHP options from config' => [
            self::createValueForTestFrameworkExtraOptions(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'phpunit',
                '--debug',
                null,
                '--debug',
            ),
        ];

        yield 'test framework PHP options from input' => [
            self::createValueForTestFrameworkExtraOptions(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'phpunit',
                null,
                '--debug',
                '--debug',
            ),
        ];

        yield 'test framework PHP options from config & input' => [
            self::createValueForTestFrameworkExtraOptions(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'phpunit',
                '--stop-on-failure',
                '--debug',
                '--debug',
            ),
        ];

        yield 'test framework PHP options from config with phpspec framework' => [
            self::createValueForTestFrameworkExtraOptions(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'phpspec',
                '--debug',
                null,
                '--debug',
            ),
        ];

        yield 'test no static analysis tool options' => [
            self::createValueForStaticAnalysisToolOptions(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                null,
                null,
            ),
        ];

        yield 'test static analysis tool options from config' => [
            self::createValueForStaticAnalysisToolOptions(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                '--memory-limit=-1',
                null,
                '--memory-limit=-1',
            ),
        ];

        yield 'test static analysis tool options from input' => [
            self::createValueForStaticAnalysisToolOptions(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                null,
                '--memory-limit=-1',
                '--memory-limit=-1',
            ),
        ];

        yield 'test static analysis tool options from config & input' => [
            self::createValueForStaticAnalysisToolOptions(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                '--level=max',
                '--memory-limit=-1',
                '--memory-limit=-1',
            ),
        ];

        yield 'PHPUnit test framework' => [
            self::createValueForTestFrameworkKey(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'phpunit',
                '--debug',
                '--debug',
            ),
        ];

        yield 'phpSpec test framework' => [
            self::createValueForTestFrameworkKey(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'phpspec',
                '--debug',
                '--debug',
            ),
        ];

        yield 'codeception test framework' => [
            self::createValueForTestFrameworkKey(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                'codeception',
                '--debug',
                '--debug',
            ),
        ];

        yield 'no mutator' => [
            self::createValueForMutators(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                [],
                '',
                false,
                self::getDefaultMutators(),
            ),
        ];

        yield 'mutators from config' => [
            self::createValueForMutators(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
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
            ),
        ];

        yield 'noop mutators from config' => [
            self::createValueForMutators(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
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
            ),
        ];

        yield 'ignore source code by regex' => [
            self::createValueForIgnoreSourceCodeByRegex(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
                [
                    '@default' => false,
                    'MethodCallRemoval' => (object) [
                        'ignoreSourceCodeByRegex' => ['Assert::.*'],
                    ],
                ],
                ['MethodCallRemoval' => ['Assert::.*']],
            ),
        ];

        yield 'ignore source code by regex with duplicates' => [
            self::createValueForIgnoreSourceCodeByRegex(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
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
            ),
        ];

        yield 'mutators from config & input' => [
            self::createValueForMutators(
                $defaultScenario,
                $defaultSchemaBuilder,
                $defaultConfigurationBuilder,
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
            ),
        ];

        yield 'with source files' => [
            $defaultScenario
                ->withSchema(
                    $defaultSchemaBuilder
                        ->withSource(new Source(['src/'], ['vendor/']))
                        ->withLogs(Logs::createEmpty())
                        ->withThreads(5)
                )
                ->withInput(
                    $defaultInputBuilder
                        ->withFilter('src/Foo.php, src/Bar.php')
                        ->withGitDiffFilter(null)
                        ->withUseGitHubLogger(false)
                )
                ->withExpected(
                    $defaultConfigurationBuilder
                        ->withSourceDirectories('src/')
                        ->withSourceFiles([
                            new SplFileInfo('src/Foo.php', 'src/Foo.php', 'src/Foo.php'),
                            new SplFileInfo('src/Bar.php', 'src/Bar.php', 'src/Bar.php'),
                        ])
                        ->withSourceFilesFilter('src/Foo.php, src/Bar.php')
                        ->withSourceFilesExcludes('vendor/')
                        ->withLogs(Logs::createEmpty())
                        ->build(),
                ),
        ];

        yield 'with absolute source directory paths' => [
            $defaultScenario
                ->withSchema(
                    $defaultSchemaBuilder
                        ->withSource(new Source(['/absolute/src/'], ['vendor/']))
                        ->withLogs(Logs::createEmpty())
                        ->withThreads(5)
                )
                ->withInput(
                    $defaultInputBuilder
                        ->withFilter('src/Foo.php, src/Bar.php')
                        ->withGitDiffFilter(null)
                        ->withUseGitHubLogger(false)
                )
                ->withExpected(
                    $defaultConfigurationBuilder
                        ->withSourceDirectories('/absolute/src/')
                        ->withSourceFiles([
                            new SplFileInfo('src/Foo.php', 'src/Foo.php', 'src/Foo.php'),
                            new SplFileInfo('src/Bar.php', 'src/Bar.php', 'src/Bar.php'),
                        ])
                        ->withSourceFilesFilter('src/Foo.php, src/Bar.php')
                        ->withSourceFilesExcludes('vendor/')
                        ->withLogs(Logs::createEmpty())
                        ->build(),
                ),
        ];

        yield 'complete' => [
            ConfigurationFactoryScenario::create(
                ciDetected: false,
                githubActionsDetected: false,
                schemaBuilder: SchemaConfigurationBuilder::withMinimalTestData()
                    ->withTimeout(10.0)
                    ->withSource(new Source(['src/'], ['vendor/']))
                    ->withLogs(
                        LogsBuilder::withCompleteTestData()
                            ->withTextLogFilePath('/text.log')
                            ->withHtmlLogFilePath('/report.html')
                            ->withSummaryLogFilePath('/summary.log')
                            ->withJsonLogFilePath('/json.log')
                            ->withGitlabLogFilePath('/gitlab.log')
                            ->withDebugLogFilePath('/debug.log')
                            ->withPerMutatorFilePath('/mutator.log')
                            ->withUseGitHubAnnotationsLogger(true)
                            ->withStrykerConfig(StrykerConfig::forFullReport('master'))
                            ->withSummaryJsonLogFilePath('/summary.json')
                            ->build(),
                    )
                    ->withTmpDir('config/tmp')
                    ->withPhpUnit(new PhpUnit('config/phpunit-dir', '/path/to/config/phpunit'))
                    ->withPhpStan(new PhpStan('config/phpstan-dir', '/path/to/bin/phpstan'))
                    ->withMutators(['@default' => true])
                    ->withTestFramework('phpunit')
                    ->withBootstrap(__DIR__ . '/../../Fixtures/Files/bootstrap/bootstrap.php')
                    ->withInitialTestsPhpOptions('-d zend_extension=wrong_xdebug.so')
                    ->withTestFrameworkExtraOptions('--debug')
                    ->withStaticAnalysisToolOptions('--memory-limit=-1')
                    ->withThreads('max')
                    ->withStaticAnalysisTool('phpstan'),
                inputBuilder: new ConfigurationFactoryInputBuilder(
                    existingCoveragePath: 'dist/coverage',
                    initialTestsPhpOptions: '-d zend_extension=xdebug.so',
                    skipInitialTests: false,
                    logVerbosity: LogVerbosity::NONE,
                    debug: true,
                    withUncovered: true,
                    noProgress: true,
                    ignoreMsiWithNoMutations: true,
                    minMsi: 72.3,
                    numberOfShownMutations: 20,
                    minCoveredMsi: 81.5,
                    msiPrecision: 2,
                    mutatorsInput: 'TrueValue',
                    testFramework: 'phpspec',
                    testFrameworkExtraOptions: '--stop-on-failure',
                    staticAnalysisToolOptions: null,
                    filter: 'src/Foo.php, src/Bar.php',
                    threadCount: 4,
                    dryRun: true,
                    gitDiffFilter: null,
                    isForGitDiffLines: false,
                    gitDiffBase: 'master',
                    useGitHubLogger: false,
                    gitlabLogFilePath: null,
                    htmlLogFilePath: null,
                    textLogFilePath: null,
                    useNoopMutators: false,
                    executeOnlyCoveringTestCases: false,
                    mapSourceClassToTestStrategy: MapSourceClassToTestStrategy::SIMPLE,
                    loggerProjectRootDirectory: null,
                    staticAnalysisTool: StaticAnalysisToolTypes::PHPSTAN,
                    mutantId: 'h4sh',
                ),
                expected: ConfigurationBuilder::withMinimalTestData()
                    ->withTimeout(10)
                    ->withSourceDirectories('src/')
                    ->withSourceFiles([
                        new SplFileInfo('src/Foo.php', 'src/Foo.php', 'src/Foo.php'),
                        new SplFileInfo('src/Bar.php', 'src/Bar.php', 'src/Bar.php'),
                    ])
                    ->withSourceFilesFilter('src/Foo.php, src/Bar.php')
                    ->withSourceFilesExcludes('vendor/')
                    ->withLogs(
                        LogsBuilder::withMinimalTestData()
                            ->withTextLogFilePath('/text.log')
                            ->withHtmlLogFilePath('/report.html')
                            ->withSummaryLogFilePath('/summary.log')
                            ->withJsonLogFilePath('/json.log')
                            ->withGitlabLogFilePath('/gitlab.log')
                            ->withDebugLogFilePath('/debug.log')
                            ->withPerMutatorFilePath('/mutator.log')
                            ->withUseGitHubAnnotationsLogger(true)
                            ->withStrykerConfig(StrykerConfig::forFullReport('master'))
                            ->withSummaryJsonLogFilePath('/summary.json')
                            ->build(),
                    )
                    ->withLogVerbosity(LogVerbosity::NONE)
                    ->withTmpDir('/path/to/config/tmp/infection')
                    ->withPhpUnit(new PhpUnit('/path/to/config/phpunit-dir', '/path/to/config/phpunit'))
                    ->withPhpStan(new PhpStan('/path/to/config/phpstan-dir', '/path/to/bin/phpstan'))
                    ->withMutators([
                        'TrueValue' => new TrueValue(new TrueValueConfig([])),
                    ])
                    ->withTestFramework('phpspec')
                    ->withBootstrap(__DIR__ . '/../../Fixtures/Files/bootstrap/bootstrap.php')
                    ->withInitialTestsPhpOptions('-d zend_extension=xdebug.so')
                    ->withSkipInitialTests(false)
                    ->withTestFrameworkExtraOptions('--stop-on-failure')
                    ->withStaticAnalysisToolOptions('--memory-limit=-1')
                    ->withCoveragePath('/path/to/dist/coverage')
                    ->withSkipCoverage(true)
                    ->withDebug(true)
                    ->withUncovered(true)
                    ->withNoProgress(true)
                    ->withIgnoreMsiWithNoMutations(true)
                    ->withMinMsi(72.3)
                    ->withNumberOfShownMutations(20)
                    ->withMinCoveredMsi(81.5)
                    ->withMsiPrecision(2)
                    ->withThreadCount(4)
                    ->withDryRun(true)
                    ->withIgnoreSourceCodeMutatorsMap([])
                    ->withExecuteOnlyCoveringTestCases(false)
                    ->withIsForGitDiffLines(false)
                    ->withGitDiffBase('master')
                    ->withMapSourceClassToTestStrategy(MapSourceClassToTestStrategy::SIMPLE)
                    ->withLoggerProjectRootDirectory(null)
                    ->withStaticAnalysisTool(StaticAnalysisToolTypes::PHPSTAN)
                    ->withMutantId('h4sh')
                    ->build(),
            ),
        ];

        yield 'custom mutator with bootstrap file' => [
            $defaultScenario
                ->withSchema(
                    $defaultSchemaBuilder
                        ->withSource(new Source([], []))
                        ->withLogs(LogsBuilder::withMinimalTestData()->build())
                        ->withTmpDir('')
                        ->withMutators(['@default' => false, 'CustomMutator' => true])
                        ->withBootstrap(__DIR__ . '/../../Fixtures/Files/bootstrap/bootstrap.php')
                )
                ->withInput(
                    $defaultInputBuilder
                        ->withUseGitHubLogger(false)
                )
                ->withExpected(
                    $defaultConfigurationBuilder
                        ->withSourceDirectories()
                        ->withSourceFiles([])
                        ->withSourceFilesExcludes()
                        ->withLogs(Logs::createEmpty())
                        ->withMutators([
                            'CustomMutator' => new CustomMutator(),
                        ])
                        ->withBootstrap(__DIR__ . '/../../Fixtures/Files/bootstrap/bootstrap.php')
                        ->build(),
                ),
        ];
    }

    private static function createValueForTimeout(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        ?float $schemaTimeout,
        float $expectedTimeout,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withTimeout($schemaTimeout)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withTimeout($expectedTimeout)
                    ->build(),
            );
    }

    private static function createValueForTmpDir(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        ?string $configTmpDir,
        ?string $expectedTmpDir,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withTmpDir($configTmpDir)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withTmpDir($expectedTmpDir)
                    ->withCoveragePath($expectedTmpDir)
                    ->build(),
            );
    }

    private static function createValueForCoveragePath(
        ConfigurationFactoryScenario $defaultScenario,
        ConfigurationBuilder $defaultConfigurationBuilder,
        ?string $existingCoveragePath,
        bool $expectedSkipCoverage,
        string $expectedCoveragePath,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withExistingCoveragePath($existingCoveragePath),
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withCoveragePath($expectedCoveragePath)
                    ->withSkipCoverage($expectedSkipCoverage)
                    ->build(),
            );
    }

    private static function createValueForPhpUnitConfigDir(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        ?string $phpUnitConfigDir,
        ?string $expectedPhpUnitConfigDir,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withPhpUnit(new PhpUnit($phpUnitConfigDir, null))
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withPhpUnit(new PhpUnit($expectedPhpUnitConfigDir, null))
                    ->build(),
            );
    }

    private static function createValueForNoProgress(
        ConfigurationFactoryScenario $defaultScenario,
        ConfigurationBuilder $defaultConfigurationBuilder,
        bool $ciDetected,
        bool $noProgress,
        bool $expectedNoProgress,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withCiDetected($ciDetected)
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withNoProgress($noProgress),
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withNoProgress($expectedNoProgress)
                    ->build(),
            );
    }

    private static function createValueForGithubActionsDetected(
        ConfigurationFactoryScenario $defaultScenario,
        LogsBuilder $defaultLogsBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        ?bool $inputUseGitHubAnnotationsLogger,
        bool $githubActionsDetected,
        bool $useGitHubAnnotationsLogger,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withGithubActionsDetected($githubActionsDetected)
            ->withSchema(
                SchemaConfigurationBuilder::from($defaultScenario->schemaBuilder->build())
                    ->withLogs(Logs::createEmpty())
            )
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withUseGitHubLogger($inputUseGitHubAnnotationsLogger)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withLogs(
                        $defaultLogsBuilder
                            ->withUseGitHubAnnotationsLogger($useGitHubAnnotationsLogger)
                            ->build(),
                    )
                    ->build(),
            );
    }

    private static function createValueForGitlabLogger(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        LogsBuilder $defaultLogsBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        ?string $gitlabFileLogPathInConfig,
        ?string $gitlabFileLogPathFromCliOption,
        ?string $expectedGitlabFileLogPath,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withLogs(
                        LogsBuilder::withMinimalTestData()
                            ->withGitlabLogFilePath($gitlabFileLogPathInConfig)
                            ->build(),
                    )
            )
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withGitlabLogFilePath($gitlabFileLogPathFromCliOption)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withLogs(
                        $defaultLogsBuilder
                            ->withGitlabLogFilePath($expectedGitlabFileLogPath)
                            ->build(),
                    )
                    ->build(),
            );
    }

    private static function createValueForIgnoreMsiWithNoMutations(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        ?bool $ignoreMsiWithNoMutationsFromSchemaConfiguration,
        ?bool $ignoreMsiWithNoMutationsFromInput,
        ?bool $expectedIgnoreMsiWithNoMutations,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withPhpUnit(new PhpUnit('/path/to', null))
                    ->withPhpStan(new PhpStan('/path/to', null))
                    ->withIgnoreMsiWithNoMutations($ignoreMsiWithNoMutationsFromSchemaConfiguration)
            )
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withIgnoreMsiWithNoMutations($ignoreMsiWithNoMutationsFromInput)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withPhpUnit(new PhpUnit('/path/to', null))
                    ->withPhpStan(new PhpStan('/path/to', null))
                    ->withIgnoreMsiWithNoMutations($expectedIgnoreMsiWithNoMutations)
                    ->build(),
            );
    }

    private static function createValueForMinMsi(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        ?float $minMsiFromSchemaConfiguration,
        ?float $minMsiFromInput,
        ?float $expectedMinMsi,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withPhpUnit(new PhpUnit('/path/to', null))
                    ->withPhpStan(new PhpStan('/path/to', null))
                    ->withMinMsi($minMsiFromSchemaConfiguration)
            )
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withMinMsi($minMsiFromInput)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withPhpUnit(new PhpUnit('/path/to', null))
                    ->withPhpStan(new PhpStan('/path/to', null))
                    ->withMinMsi($expectedMinMsi)
                    ->build(),
            );
    }

    private static function createValueForMinCoveredMsi(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        ?float $minCoveredMsiFromSchemaConfiguration,
        ?float $minCoveredMsiFromInput,
        ?float $expectedMinCoveredMsi,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withPhpUnit(new PhpUnit('/path/to', null))
                    ->withPhpStan(new PhpStan('/path/to', null))
                    ->withMinCoveredMsi($minCoveredMsiFromSchemaConfiguration)
            )
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withMinCoveredMsi($minCoveredMsiFromInput)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withPhpUnit(new PhpUnit('/path/to', null))
                    ->withPhpStan(new PhpStan('/path/to', null))
                    ->withMinCoveredMsi($expectedMinCoveredMsi)
                    ->build(),
            );
    }

    private static function createValueForTestFramework(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        ?string $configTestFramework,
        ?string $inputTestFramework,
        string $expectedTestFramework,
        string $expectedTestFrameworkExtraOptions,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withTestFramework($configTestFramework)
            )
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withTestFramework($inputTestFramework)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withTestFramework($expectedTestFramework)
                    ->withTestFrameworkExtraOptions($expectedTestFrameworkExtraOptions)
                    ->build(),
            );
    }

    private static function createValueForStaticAnalysisTool(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        ?string $configStaticAnalysisTool,
        ?string $inputStaticAnalysisTool,
        ?string $expectedStaticAnalysisTool,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withTestFramework(TestFrameworkTypes::PHPUNIT)
                    ->withStaticAnalysisTool($configStaticAnalysisTool)
            )
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withStaticAnalysisTool($inputStaticAnalysisTool)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withStaticAnalysisTool($expectedStaticAnalysisTool)
                    ->build(),
            );
    }

    private static function createValueForInitialTestsPhpOptions(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        ?string $configInitialTestsPhpOptions,
        ?string $inputInitialTestsPhpOptions,
        ?string $expectedInitialTestPhpOptions,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withInitialTestsPhpOptions($configInitialTestsPhpOptions)
            )
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withInitialTestsPhpOptions($inputInitialTestsPhpOptions)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withInitialTestsPhpOptions($expectedInitialTestPhpOptions)
                    ->build(),
            );
    }

    private static function createValueForTestFrameworkExtraOptions(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        string $configTestFramework,
        ?string $configTestFrameworkExtraOptions,
        ?string $inputTestFrameworkExtraOptions,
        string $expectedTestFrameworkExtraOptions,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withTestFramework($configTestFramework)
                    ->withTestFrameworkExtraOptions($configTestFrameworkExtraOptions)
            )
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withTestFrameworkExtraOptions($inputTestFrameworkExtraOptions)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withTestFramework($configTestFramework)
                    ->withTestFrameworkExtraOptions($expectedTestFrameworkExtraOptions)
                    ->build(),
            );
    }

    private static function createValueForStaticAnalysisToolOptions(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        ?string $configStaticAnalysisToolOptions,
        ?string $inputStaticAnalysisToolOptions,
        ?string $expectedStaticAnalysisToolOptions,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withStaticAnalysisToolOptions($configStaticAnalysisToolOptions)
            )
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withStaticAnalysisToolOptions($inputStaticAnalysisToolOptions)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withStaticAnalysisToolOptions($expectedStaticAnalysisToolOptions)
                    ->build(),
            );
    }

    private static function createValueForTestFrameworkKey(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        string $configTestFramework,
        string $inputTestFrameworkExtraOptions,
        string $expectedTestFrameworkExtraOptions,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withTestFramework($configTestFramework)
            )
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withTestFrameworkExtraOptions($inputTestFrameworkExtraOptions)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withTestFramework($configTestFramework)
                    ->withTestFrameworkExtraOptions($expectedTestFrameworkExtraOptions)
                    ->build(),
            );
    }

    /**
     * @param array<string, Mutator> $expectedMutators
     * @param array<string, array<int, string>> $expectedIgnoreSourceCodeMutatorsMap
     */
    private static function createValueForMutators(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        array $configMutators,
        string $inputMutators,
        bool $useNoopMutators,
        array $expectedMutators,
        array $expectedIgnoreSourceCodeMutatorsMap = [],
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withMutators($configMutators)
            )
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withMutatorsInput($inputMutators)
                    ->withUseNoopMutators($useNoopMutators)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withMutators($expectedMutators)
                    ->withIgnoreSourceCodeMutatorsMap($expectedIgnoreSourceCodeMutatorsMap)
                    ->build(),
            );
    }

    /**
     * @param array<string, mixed> $configMutators
     * @param array<string, array<int, string>> $expectedIgnoreSourceCodeMutatorsMap
     */
    private static function createValueForIgnoreSourceCodeByRegex(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        array $configMutators,
        array $expectedIgnoreSourceCodeMutatorsMap,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withMutators($configMutators)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withMutators([
                        'MethodCallRemoval' => new MethodCallRemoval(),
                    ])
                    ->withIgnoreSourceCodeMutatorsMap($expectedIgnoreSourceCodeMutatorsMap)
                    ->build(),
            );
    }

    private static function createValueForHtmlLogFilePath(
        ConfigurationFactoryScenario $defaultScenario,
        SchemaConfigurationBuilder $defaultSchemaBuilder,
        LogsBuilder $defaultLogsBuilder,
        ConfigurationBuilder $defaultConfigurationBuilder,
        ?string $htmlFileLogPathInConfig,
        ?string $htmlFileLogPathFromCliOption,
        ?string $expectedHtmlFileLogPath,
    ): ConfigurationFactoryScenario {
        return $defaultScenario
            ->withSchema(
                $defaultSchemaBuilder
                    ->withLogs(
                        LogsBuilder::withMinimalTestData()
                            ->withHtmlLogFilePath($htmlFileLogPathInConfig)
                            ->build(),
                    )
            )
            ->withInput(
                $defaultScenario->inputBuilder
                    ->withHtmlLogFilePath($htmlFileLogPathFromCliOption)
            )
            ->withExpected(
                $defaultConfigurationBuilder
                    ->withLogs(
                        $defaultLogsBuilder
                            ->withHtmlLogFilePath($expectedHtmlFileLogPath)
                            ->build(),
                    )
                    ->build(),
            );
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
            ->willReturnCallback(
                static function (array $source, array $excludes) use ($schema) {
                    $schemaSourceDirs = $schema->getSource()->getDirectories();

                    // ConfigurationFactory::collectFiles() MUST convert relative paths to absolute paths
                    // relative to the schema file location (e.g., 'src/' → '/path/to/src')
                    // Absolute paths should be passed through unchanged

                    // For relative paths like ['src/'], expect transformation to ['/path/to/src']
                    if ($schemaSourceDirs === ['src/']) {
                        if ($source !== ['/path/to/src']) {
                            throw new LogicException(
                                sprintf(
                                    'Expected source directories to be transformed to absolute paths. Expected: ["/path/to/src"], got: %s',
                                    var_export($source, true),
                                ),
                            );
                        }
                    }

                    // For absolute paths like ['/absolute/src/'], expect no transformation
                    if ($schemaSourceDirs === ['/absolute/src/']) {
                        if ($source !== ['/absolute/src/']) {
                            throw new LogicException(
                                sprintf(
                                    'Expected absolute source directories to be passed through unchanged. Expected: ["/absolute/src/"], got: %s',
                                    var_export($source, true),
                                ),
                            );
                        }
                    }

                    if ($excludes === ['vendor/']) {
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
