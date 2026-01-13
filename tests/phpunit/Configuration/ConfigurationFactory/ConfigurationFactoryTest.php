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
use Infection\Configuration\SourceFilter\GitDiffFilter;
use Infection\Configuration\SourceFilter\IncompleteGitDiffFilter;
use Infection\Configuration\SourceFilter\PlainFilter;
use Infection\Console\LogVerbosity;
use Infection\FileSystem\TmpDirProvider;
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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use function sys_get_temp_dir;

#[Group('integration')]
#[CoversClass(ConfigurationFactory::class)]
final class ConfigurationFactoryTest extends TestCase
{
    private const GIT_DEFAULT_BASE = 'test/default';

    /**
     * @var array<string, Mutator>|null
     */
    private static ?array $mutators = null;

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
            pathname: '/path/to/infection.json',
            timeout: null,
            source: new Source([], []),
            logs: Logs::createEmpty(),
            tmpDir: '',
            phpUnit: new PhpUnit(null, null),
            phpStan: new PhpStan(null, null),
            ignoreMsiWithNoMutations: null,
            minMsi: null,
            minCoveredMsi: null,
            timeoutsAsEscaped: null,
            maxTimeouts: null,
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
                timeoutsAsEscaped: false,
                maxTimeouts: null,
                msiPrecision: 2,
                mutatorsInput: '',
                testFramework: TestFrameworkTypes::PHPUNIT,
                testFrameworkExtraOptions: null,
                staticAnalysisToolOptions: null,
                sourceFilter: null,
                threadCount: 0,
                dryRun: false,
                useGitHubLogger: false,
                gitlabLogFilePath: null,
                htmlLogFilePath: null,
                textLogFilePath: null,
                summaryJsonLogFilePath: null,
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
            pathname: '/path/to/infection.json',
            timeout: null,
            source: new Source([], []),
            logs: Logs::createEmpty(),
            tmpDir: '',
            phpUnit: new PhpUnit(null, null),
            phpStan: new PhpStan(null, null),
            ignoreMsiWithNoMutations: null,
            minMsi: null,
            minCoveredMsi: null,
            timeoutsAsEscaped: null,
            maxTimeouts: null,
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
            timeoutsAsEscaped: false,
            maxTimeouts: null,
            msiPrecision: 2,
            mutatorsInput: '',
            testFramework: null,
            testFrameworkExtraOptions: null,
            staticAnalysisToolOptions: null,
            sourceFilter: new IncompleteGitDiffFilter('AM', 'master'),
            threadCount: 1,
            dryRun: false,
            useGitHubLogger: true,
            gitlabLogFilePath: null,
            htmlLogFilePath: null,
            textLogFilePath: null,
            summaryJsonLogFilePath: null,
            useNoopMutators: false,
            executeOnlyCoveringTestCases: true,
            mapSourceClassToTestStrategy: MapSourceClassToTestStrategy::SIMPLE,
            loggerProjectRootDirectory: null,
            staticAnalysisTool: null,
            mutantId: null,
        );

        $defaultConfiguration = new Configuration(
            processTimeout: 10,
            source: new Source(),
            sourceFilter: new GitDiffFilter('AM', 'reference(master)'),
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
            isDebugEnabled: false,
            withUncovered: false,
            noProgress: false,
            ignoreMsiWithNoMutations: false,
            minMsi: null,
            numberOfShownMutations: 0,
            minCoveredMsi: null,
            timeoutsAsEscaped: false,
            maxTimeouts: null,
            msiPrecision: 2,
            threadCount: 1,
            isDryRun: false,
            ignoreSourceCodeMutatorsMap: [],
            executeOnlyCoveringTestCases: true,
            mapSourceClassToTestStrategy: MapSourceClassToTestStrategy::SIMPLE,
            loggerProjectRootDirectory: null,
            staticAnalysisTool: null,
            mutantId: null,
            configurationPathname: '/path/to/infection.json',
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

        yield 'null html file log path' => [
            $defaultScenario->forValueForHtmlLogFilePath(
                '/path/to/from-config.html',
                null,
                '/path/to/from-config.html',
            ),
        ];

        yield 'absolute html file log path' => [
            $defaultScenario->forValueForHtmlLogFilePath(
                '/path/to/from-config.html',
                null,
                '/path/to/from-config.html',
            ),
        ];

        yield 'relative html file log path' => [
            $defaultScenario->forValueForHtmlLogFilePath(
                'relative/path/to/from-config.html',
                null,
                '/path/to/relative/path/to/from-config.html',
            ),
        ];

        yield 'override html file log path from CLI option with existing path from config file' => [
            $defaultScenario->forValueForHtmlLogFilePath(
                '/from-config.html',
                '/from-cli.html',
                '/from-cli.html',
            ),
        ];

        yield 'set html file log path from CLI option when config file has no setting' => [
            $defaultScenario->forValueForHtmlLogFilePath(
                null,
                '/from-cli.html',
                '/from-cli.html',
            ),
        ];

        yield 'null html file log path in config and CLI' => [
            $defaultScenario->forValueForHtmlLogFilePath(
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
            $defaultScenario->forValueForTimeout(
                null,
                10,
            ),
        ];

        yield 'config timeout' => [
            $defaultScenario->forValueForTimeout(
                20,
                20,
            ),
        ];

        yield 'null tmp dir' => [
            $defaultScenario->forValueForTmpDir(
                null,
                sys_get_temp_dir() . '/infection',
            ),
        ];

        yield 'empty tmp dir' => [
            $defaultScenario->forValueForTmpDir(
                '',
                sys_get_temp_dir() . '/infection',
            ),
        ];

        yield 'relative tmp dir path' => [
            $defaultScenario->forValueForTmpDir(
                'relative/path/to/tmp',
                '/path/to/relative/path/to/tmp/infection',
            ),
        ];

        yield 'absolute tmp dir path' => [
            $defaultScenario->forValueForTmpDir(
                '/absolute/path/to/tmp',
                '/absolute/path/to/tmp/infection',
            ),
        ];

        yield 'no existing base path for code coverage' => [
            $defaultScenario->forValueForCoveragePath(
                null,
                false,
                sys_get_temp_dir() . '/infection',
            ),
        ];

        yield 'absolute base path for code coverage' => [
            $defaultScenario->forValueForCoveragePath(
                '/path/to/coverage',
                true,
                '/path/to/coverage',
            ),
        ];

        yield 'relative base path for code coverage' => [
            $defaultScenario->forValueForCoveragePath(
                'relative/path/to/coverage',
                true,
                '/path/to/relative/path/to/coverage',
            ),
        ];

        yield 'no PHPUnit config dir' => [
            $defaultScenario->forValueForPhpUnitConfigDir(
                'relative/path/to/phpunit/config',
                '/path/to/relative/path/to/phpunit/config',
            ),
        ];

        yield 'relative PHPUnit config dir' => [
            $defaultScenario->forValueForPhpUnitConfigDir(
                'relative/path/to/phpunit/config',
                '/path/to/relative/path/to/phpunit/config',
            ),
        ];

        yield 'absolute PHPUnit config dir' => [
            $defaultScenario->forValueForPhpUnitConfigDir(
                '/path/to/phpunit/config',
                '/path/to/phpunit/config',
            ),
        ];

        yield 'progress in non-CI environment' => [
            $defaultScenario->forValueForNoProgress(
                false,
                false,
                false,
            ),
        ];

        yield 'progress in CI environment' => [
            $defaultScenario->forValueForNoProgress(
                true,
                false,
                true,
            ),
        ];

        yield 'no progress in non-CI environment' => [
            $defaultScenario->forValueForNoProgress(
                false,
                true,
                true,
            ),
        ];

        yield 'no progress in CI environment' => [
            $defaultScenario->forValueForNoProgress(
                true,
                true,
                true,
            ),
        ];

        yield 'Github Actions annotation disabled, not logged in non-Github Actions environment' => [
            $defaultScenario->forValueForGithubActionsDetected(
                false,
                false,
                false,
            ),
        ];

        yield 'Github Actions annotation disabled, not logged in Github Actions environment' => [
            $defaultScenario->forValueForGithubActionsDetected(
                false,
                true,
                false,
            ),
        ];

        yield 'Github Actions annotation not provided, not logged in non-Github Actions environment' => [
            $defaultScenario->forValueForGithubActionsDetected(
                null,
                false,
                false,
            ),
        ];

        yield 'Github Actions annotation not provided, logged in Github Actions environment' => [
            $defaultScenario->forValueForGithubActionsDetected(
                null,
                true,
                true,
            ),
        ];

        yield 'Github Actions annotation enabled, logged in non-Github Actions environment' => [
            $defaultScenario->forValueForGithubActionsDetected(
                true,
                false,
                true,
            ),
        ];

        yield 'Github Actions annotation enabled, logged in Github Actions environment' => [
            $defaultScenario->forValueForGithubActionsDetected(
                true,
                true,
                true,
            ),
        ];

        yield 'null GitLab file log path with existing path from config file' => [
            $defaultScenario->forValueForGitlabLogger(
                '/from-config.json',
                null,
                '/from-config.json',
            ),
        ];

        yield 'absolute GitLab file log path' => [
            $defaultScenario->forValueForGitlabLogger(
                '/path/to/from-config.json',
                null,
                '/path/to/from-config.json',
            ),
        ];

        yield 'relative GitLab file log path' => [
            $defaultScenario->forValueForGitlabLogger(
                'relative/path/to/from-config.json',
                null,
                '/path/to/relative/path/to/from-config.json',
            ),
        ];

        yield 'override GitLab file log path from CLI option with existing path from config file' => [
            $defaultScenario->forValueForGitlabLogger(
                '/from-config.json',
                '/from-cli.json',
                '/from-cli.json',
            ),
        ];

        yield 'set GitLab file log path from CLI option when config file has no setting' => [
            $defaultScenario->forValueForGitlabLogger(
                null,
                '/from-cli.json',
                '/from-cli.json',
            ),
        ];

        yield 'null GitLab file log path in config and CLI' => [
            $defaultScenario->forValueForGitlabLogger(
                null,
                null,
                null,
            ),
        ];

        yield 'ignoreMsiWithNoMutations not specified in schema and true in input' => [
            $defaultScenario->forValueForIgnoreMsiWithNoMutations(
                null,
                true,
                true,
            ),
        ];

        yield 'ignoreMsiWithNoMutations not specified in schema and false in input' => [
            $defaultScenario->forValueForIgnoreMsiWithNoMutations(
                null,
                false,
                false,
            ),
        ];

        yield 'ignoreMsiWithNoMutations true in schema and not specified in input' => [
            $defaultScenario->forValueForIgnoreMsiWithNoMutations(
                true,
                null,
                true,
            ),
        ];

        yield 'ignoreMsiWithNoMutations false in schema and not specified in input' => [
            $defaultScenario->forValueForIgnoreMsiWithNoMutations(
                false,
                null,
                false,
            ),
        ];

        yield 'ignoreMsiWithNoMutations true in schema and false in input' => [
            $defaultScenario->forValueForIgnoreMsiWithNoMutations(
                true,
                false,
                false,
            ),
        ];

        yield 'ignoreMsiWithNoMutations false in schema and true in input' => [
            $defaultScenario->forValueForIgnoreMsiWithNoMutations(
                false,
                true,
                true,
            ),
        ];

        yield 'minMsi not specified in schema and not specified in input' => [
            $defaultScenario->forValueForMinMsi(
                null,
                null,
                null,
            ),
        ];

        yield 'minMsi specified in schema and not specified in input' => [
            $defaultScenario->forValueForMinMsi(
                33.3,
                null,
                33.3,
            ),
        ];

        yield 'minMsi not specified in schema and specified in input' => [
            $defaultScenario->forValueForMinMsi(
                null,
                21.2,
                21.2,
            ),
        ];

        yield 'minMsi specified in schema and specified in input' => [
            $defaultScenario->forValueForMinMsi(
                33.3,
                21.2,
                21.2,
            ),
        ];

        yield 'minCoveredMsi not specified in schema and not specified in input' => [
            $defaultScenario->forValueForMinCoveredMsi(
                null,
                null,
                null,
            ),
        ];

        yield 'minCoveredMsi specified in schema and not specified in input' => [
            $defaultScenario->forValueForMinCoveredMsi(
                33.3,
                null,
                33.3,
            ),
        ];

        yield 'minCoveredMsi not specified in schema and specified in input' => [
            $defaultScenario->forValueForMinCoveredMsi(
                null,
                21.2,
                21.2,
            ),
        ];

        yield 'minCoveredMsi specified in schema and specified in input' => [
            $defaultScenario->forValueForMinCoveredMsi(
                33.3,
                21.2,
                21.2,
            ),
        ];

        yield 'timeoutsAsEscaped not specified in schema and false in input' => [
            $defaultScenario->forValueForTimeoutsAsEscaped(
                null,
                false,
                false,
            ),
        ];

        yield 'timeoutsAsEscaped not specified in schema and true in input' => [
            $defaultScenario->forValueForTimeoutsAsEscaped(
                null,
                true,
                true,
            ),
        ];

        yield 'timeoutsAsEscaped false in schema and false in input' => [
            $defaultScenario->forValueForTimeoutsAsEscaped(
                false,
                false,
                false,
            ),
        ];

        yield 'timeoutsAsEscaped true in schema and false in input' => [
            $defaultScenario->forValueForTimeoutsAsEscaped(
                true,
                false,
                true,
            ),
        ];

        yield 'timeoutsAsEscaped false in schema and true in input' => [
            $defaultScenario->forValueForTimeoutsAsEscaped(
                false,
                true,
                true,
            ),
        ];

        yield 'timeoutsAsEscaped true in schema and true in input' => [
            $defaultScenario->forValueForTimeoutsAsEscaped(
                true,
                true,
                true,
            ),
        ];

        yield 'maxTimeouts not specified in schema and not specified in input' => [
            $defaultScenario->forValueForMaxTimeouts(
                null,
                null,
                null,
            ),
        ];

        yield 'maxTimeouts specified in schema and not specified in input' => [
            $defaultScenario->forValueForMaxTimeouts(
                10,
                null,
                10,
            ),
        ];

        yield 'maxTimeouts not specified in schema and specified in input' => [
            $defaultScenario->forValueForMaxTimeouts(
                null,
                5,
                5,
            ),
        ];

        yield 'maxTimeouts specified in schema and specified in input' => [
            $defaultScenario->forValueForMaxTimeouts(
                10,
                5,
                5,
            ),
        ];

        yield 'no static analysis tool' => [
            $defaultScenario->forValueForStaticAnalysisTool(
                null,
                null,
                null,
            ),
        ];

        yield 'static analysis tool from config' => [
            $defaultScenario->forValueForStaticAnalysisTool(
                'phpstan',
                null,
                'phpstan',
            ),
        ];

        yield 'static analysis tool from input' => [
            $defaultScenario->forValueForStaticAnalysisTool(
                null,
                'phpstan',
                'phpstan',
            ),
        ];

        yield 'static analysis tool from config & input' => [
            $defaultScenario->forValueForStaticAnalysisTool(
                'phpstan',
                'phpstan',
                'phpstan',
            ),
        ];

        yield 'no test framework' => [
            $defaultScenario->forValueForTestFramework(
                null,
                null,
                'phpunit',
                '',
            ),
        ];

        yield 'test framework from config' => [
            $defaultScenario->forValueForTestFramework(
                'phpspec',
                null,
                'phpspec',
                '',
            ),
        ];

        yield 'test framework from input' => [
            $defaultScenario->forValueForTestFramework(
                null,
                'phpspec',
                'phpspec',
                '',
            ),
        ];

        yield 'test framework from config & input' => [
            $defaultScenario->forValueForTestFramework(
                'phpunit',
                'phpspec',
                'phpspec',
                '',
            ),
        ];

        yield 'test no test PHP options' => [
            $defaultScenario->forValueForInitialTestsPhpOptions(
                null,
                null,
                null,
            ),
        ];

        yield 'test test PHP options from config' => [
            $defaultScenario->forValueForInitialTestsPhpOptions(
                '-d zend_extension=xdebug.so',
                null,
                '-d zend_extension=xdebug.so',
            ),
        ];

        yield 'test test PHP options from input' => [
            $defaultScenario->forValueForInitialTestsPhpOptions(
                null,
                '-d zend_extension=xdebug.so',
                '-d zend_extension=xdebug.so',
            ),
        ];

        yield 'test test PHP options from config & input' => [
            $defaultScenario->forValueForInitialTestsPhpOptions(
                '-d zend_extension=another_xdebug.so',
                '-d zend_extension=xdebug.so',
                '-d zend_extension=xdebug.so',
            ),
        ];

        yield 'test no framework PHP options' => [
            $defaultScenario->forValueForTestFrameworkExtraOptions(
                'phpunit',
                null,
                null,
                '',
            ),
        ];

        yield 'test framework PHP options from config' => [
            $defaultScenario->forValueForTestFrameworkExtraOptions(
                'phpunit',
                '--debug',
                null,
                '--debug',
            ),
        ];

        yield 'test framework PHP options from input' => [
            $defaultScenario->forValueForTestFrameworkExtraOptions(
                'phpunit',
                null,
                '--debug',
                '--debug',
            ),
        ];

        yield 'test framework PHP options from config & input' => [
            $defaultScenario->forValueForTestFrameworkExtraOptions(
                'phpunit',
                '--stop-on-failure',
                '--debug',
                '--debug',
            ),
        ];

        yield 'test framework PHP options from config with phpspec framework' => [
            $defaultScenario->forValueForTestFrameworkExtraOptions(
                'phpspec',
                '--debug',
                null,
                '--debug',
            ),
        ];

        yield 'test no static analysis tool options' => [
            $defaultScenario->forValueForStaticAnalysisToolOptions(
                null,
                null,
                null,
            ),
        ];

        yield 'test static analysis tool options from config' => [
            $defaultScenario->forValueForStaticAnalysisToolOptions(
                '--memory-limit=-1',
                null,
                '--memory-limit=-1',
            ),
        ];

        yield 'test static analysis tool options from input' => [
            $defaultScenario->forValueForStaticAnalysisToolOptions(
                null,
                '--memory-limit=-1',
                '--memory-limit=-1',
            ),
        ];

        yield 'test static analysis tool options from config & input' => [
            $defaultScenario->forValueForStaticAnalysisToolOptions(
                '--level=max',
                '--memory-limit=-1',
                '--memory-limit=-1',
            ),
        ];

        yield 'PHPUnit test framework' => [
            $defaultScenario->forValueForTestFrameworkKey(
                'phpunit',
                '--debug',
                '--debug',
            ),
        ];

        yield 'phpSpec test framework' => [
            $defaultScenario->forValueForTestFrameworkKey(
                'phpspec',
                '--debug',
                '--debug',
            ),
        ];

        yield 'codeception test framework' => [
            $defaultScenario->forValueForTestFrameworkKey(
                'codeception',
                '--debug',
                '--debug',
            ),
        ];

        yield 'no mutator' => [
            $defaultScenario->forValueForMutators(
                [],
                '',
                false,
                self::getDefaultMutators(),
            ),
        ];

        yield 'mutators from config' => [
            $defaultScenario->forValueForMutators(
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
            $defaultScenario->forValueForMutators(
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
            $defaultScenario->forValueForIgnoreSourceCodeByRegex(
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
            $defaultScenario->forValueForIgnoreSourceCodeByRegex(
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
            $defaultScenario->forValueForMutators(
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

        yield 'without any filters' => [
            $defaultScenario
                ->forSourceFilter(
                    sourceFilter: null,
                    expectedSourceFilter: null,
                ),
        ];

        yield 'without source files filters' => [
            $defaultScenario
                ->forSourceFilter(
                    sourceFilter: new PlainFilter([
                        'src/Foo.php',
                        'src/Bar.php',
                    ]),
                    expectedSourceFilter: new PlainFilter([
                        'src/Foo.php',
                        'src/Bar.php',
                    ]),
                ),
        ];

        yield 'with git filters' => [
            $defaultScenario
                ->forSourceFilter(
                    sourceFilter: new IncompleteGitDiffFilter('AD', null),
                    expectedSourceFilter: new GitDiffFilter('AD', 'reference(test/default)'),
                ),
        ];

        yield 'with git filters and base branch' => [
            $defaultScenario
                ->forSourceFilter(
                    sourceFilter: new IncompleteGitDiffFilter('AD', 'upstream/main'),
                    expectedSourceFilter: new GitDiffFilter('AD', 'reference(upstream/main)'),
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
                    timeoutsAsEscaped: false,
                    maxTimeouts: null,
                    msiPrecision: 2,
                    mutatorsInput: 'TrueValue',
                    testFramework: 'phpspec',
                    testFrameworkExtraOptions: '--stop-on-failure',
                    staticAnalysisToolOptions: null,
                    sourceFilter: new PlainFilter([
                        'src/Foo.php',
                        'src/Bar.php',
                    ]),
                    threadCount: 4,
                    dryRun: true,
                    useGitHubLogger: false,
                    gitlabLogFilePath: null,
                    htmlLogFilePath: null,
                    textLogFilePath: null,
                    summaryJsonLogFilePath: null,
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
                    ->withSourceFilter(
                        new PlainFilter([
                            'src/Foo.php',
                            'src/Bar.php',
                        ]),
                    )
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
                    ->withMapSourceClassToTestStrategy(MapSourceClassToTestStrategy::SIMPLE)
                    ->withLoggerProjectRootDirectory(null)
                    ->withStaticAnalysisTool(StaticAnalysisToolTypes::PHPSTAN)
                    ->withMutantId('h4sh')
                    ->withConfigPathname('/path/to/infection.json')
                    ->build(),
            ),
        ];

        yield 'custom mutator with bootstrap file' => [
            $defaultScenario
                ->withSchema(
                    $defaultSchemaBuilder
                        ->withLogs(LogsBuilder::withMinimalTestData()->build())
                        ->withTmpDir('')
                        ->withMutators(['@default' => false, 'CustomMutator' => true])
                        ->withBootstrap(__DIR__ . '/../../Fixtures/Files/bootstrap/bootstrap.php'),
                )
                ->withInput(
                    $defaultInputBuilder
                        ->withUseGitHubLogger(false),
                )
                ->withExpected(
                    $defaultConfigurationBuilder
                        ->withLogs(Logs::createEmpty())
                        ->withMutators([
                            'CustomMutator' => new CustomMutator(),
                        ])
                        ->withBootstrap(__DIR__ . '/../../Fixtures/Files/bootstrap/bootstrap.php')
                        ->build(),
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
    ): ConfigurationFactory {
        return new ConfigurationFactory(
            new TmpDirProvider(),
            SingletonContainer::getContainer()->getMutatorResolver(),
            SingletonContainer::getContainer()->getMutatorFactory(),
            new MutatorParser(),
            new DummyCiDetector($ciDetected, $githubActionsDetected),
            new ConfigurationFactoryGit(
                self::GIT_DEFAULT_BASE,
            ),
        );
    }
}
