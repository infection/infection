<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;
use Behat\Step\Then;
use Symfony\Component\Filesystem\Filesystem;
use Webmozart\Assert\Assert;
use function array_map;
use function sort;
use const DIRECTORY_SEPARATOR;
use const PHP_BINARY;

final class PhpUnitContext implements Context
{
    private const string PHPUNIT_CONFIGURATION_RECORDING_PATH = 'var/phpunit/initial-configuration.json';
    private const string PHPUNIT_LOADED_TESTS_RECORDING_PATH = 'var/phpunit/initial-loaded-tests.json';
    private const string PHPUNIT_EXECUTED_TESTS_RECORDING_PATH = 'var/phpunit/initial-tests.jsonl';
    private const string PHPUNIT_OUTPUT_PATH = 'var/phpunit/output.log';

    private const string TEST_IMPACT_DATA_DIR = 'var/phpunit-cache/test-impact-data';

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly ShellCommandRunner $shellCommandRunner,
        private readonly ScenarioState $scenarioState,
    ) {
    }

    #[Given('PHPUnit is configured to record test impact data from executed code without deriving it from coverage targets')]
    public function configureTestImpactRecordingFromExecutedCode(): void
    {
        $this->copyPhpUnitConfiguration(
            // Keep the path like this to be able to easily click to navigate to the path in an IDE.
            __DIR__.'/../../configurations/tia-observed.xml',
        );
    }

    #[Given('a successful PHPUnit run has executed all tests and recorded their impact data')]
    public function runAllTestsAndRecordImpactData(): void
    {
        $this->executePhpUnit([]);

        $this->assertAllTestsWereExecuted();

        Assert::fileExists(
            $this->getTestImpactDirectory(),
            'The PHPUnit run did not create the expected test impact data file.',
        );
    }

    #[Given('PHPUnit has not been run and there is no recorded impact data')]
    public function assertPhpUnitHasNotRunAndHasNoRecordedImpactData(): void
    {
        Assert::null(
            $this->scenarioState->findLastPhpUnitExecutionResult(),
            'PHPUnit has already started in this scenario project, but this scenario requires no previous PHPUnit run.',
        );

        Assert::false(
            $this->filesystem->exists($this->getTestImpactDirectory()),
            'The scenario project already contains test impact data, but this scenario requires none.',
        );
    }

    #[Then('the initial run executes the following tests:')]
    public function assertListedTestsWereExecuted(TableNode $tests): void
    {
        $qualifyTestName = static fn (string $test): string => 'Infection\\E2ETests\\PHPUnitTIA\\Tests\\' . $test;

        $this->assertExecutedTests(
            array_map(
                $qualifyTestName,
                $tests->getColumn(0),
            ),
        );
    }

    #[Then('the initial run executes all tests')]
    public function assertAllTestsWereExecuted(): void
    {
        $this->assertExecutedTests(
            $this->scenarioState->getLastPhpUnitExecutionResult()->loadedTests,
        );
    }

    /**
     * @param list<string> $options
     */
    private function executePhpUnit(array $options): void
    {
        $logPath = $this->scenarioState->scenarioProjectDirectory . '/' . self::PHPUNIT_OUTPUT_PATH;
        $this->filesystem->dumpFile($logPath, '');

        $this->shellCommandRunner->mustRun(
            [
                PHP_BINARY,
                'vendor/bin/phpunit',
                '--configuration',
                'phpunit.xml',
                ...$options,
            ],
            callback: function (string $type, string $output) use ($logPath): void {
                $this->filesystem->appendToFile($logPath, $output);
            },
            cwd: $this->scenarioState->scenarioProjectDirectory,
            env: ['XDEBUG_MODE' => 'coverage'],
            timeout: 2,
        );

        $this->scenarioState->phpUnitExecutionResults[] = $this->createExecutionResult($logPath);
    }

    /**
     * @param list<string> $expected
     */
    private function assertExecutedTests(array $expected): void
    {
        Assert::same(
            self::sortTests(
                $this->scenarioState->getLastPhpUnitExecutionResult()->executedTests,
            ),
            self::sortTests($expected),
            'The executed tests do not match the expected tests.',
        );
    }

    /**
     * @param list<string> $tests
     *
     * @return list<string>
     */
    private static function sortTests(array $tests): array
    {
        sort($tests);

        return $tests;
    }

    private function copyPhpUnitConfiguration(string $phpunitConfiguration): void
    {
        $this->filesystem->copy(
            $phpunitConfiguration,
            $this->scenarioState->scenarioProjectDirectory . '/phpunit.xml',
        );
    }

    private function getTestImpactDirectory(): string
    {
        return $this->scenarioState->scenarioProjectDirectory. DIRECTORY_SEPARATOR. self::TEST_IMPACT_DATA_DIR;
    }

    /**
     * @param string $logPath
     */
    public function createExecutionResult(string $logPath): PhpUnitExecutionResult
    {
        return PhpUnitExecutionResult::fromRecordings(
            output: $this->filesystem->readFile($logPath),
            configurationJson: $this->readPhpUnitLogFile(self::PHPUNIT_CONFIGURATION_RECORDING_PATH),
            loadedTestsJson: $this->readPhpUnitLogFile(self::PHPUNIT_LOADED_TESTS_RECORDING_PATH),
            executedTestsJsonLines: $this->readPhpUnitLogFile(self::PHPUNIT_EXECUTED_TESTS_RECORDING_PATH),
        );
    }

    private function readPhpUnitLogFile(string $filename): string
    {
        return $this->filesystem->readFile(
            $this->scenarioState->scenarioProjectDirectory.DIRECTORY_SEPARATOR.$filename,
        );
    }
}
