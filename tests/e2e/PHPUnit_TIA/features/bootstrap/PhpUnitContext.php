<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\Given;
use Behat\Step\Then;
use Behat\Step\When;
use DOMDocument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;
use function array_map;
use function basename;
use function dirname;
use function json_encode;
use function sort;
use function sprintf;
use const DIRECTORY_SEPARATOR;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const PHP_BINARY;

final class PhpUnitContext implements Context
{
    private const string PHPUNIT_CONFIGURATION_RECORDING_PATH = 'var/phpunit/configuration.json';
    private const string PHPUNIT_LOADED_TESTS_RECORDING_PATH = 'var/phpunit/loaded-tests.json';
    private const string PHPUNIT_EXECUTED_TESTS_RECORDING_PATH = 'var/phpunit/executed-tests.jsonl';
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

    #[Given('the PHPUnit configuration has these attributes:')]
    public function configureAttributes(TableNode $attributes): void
    {
        $path = $this->scenarioState->scenarioProjectDirectory . '/phpunit.xml';
        $document = new DOMDocument();
        $document->loadXML(
            $this->filesystem->readFile($path),
        );

        foreach ($attributes->getRows() as [$name, $value]) {
            $document->documentElement->setAttribute(
                $name,
                $value,
            );
        }

        $this->filesystem->dumpFile(
            $path,
            $document->saveXML(),
        );
    }

    #[Given('PHPUnit has no explicit TIA configuration')]
    public function removeTiaConfiguration(): void
    {
        $path = $this->scenarioState->scenarioProjectDirectory . '/phpunit.xml';
        $document = new DOMDocument();
        $document->loadXML(
            $this->filesystem->readFile($path),
        );
        $tiaAttributes = [
            'recordTestImpactData',
            'deriveTestImpactDataFromCoverageTargets',
            'recordTestRunHistory',
        ];

        foreach ($tiaAttributes as $attribute) {
            $document->documentElement->removeAttribute($attribute);
        }

        $this->filesystem->dumpFile(
            $path,
            $document->saveXML(),
        );
    }

    #[When('I run PHPUnit with options:')]
    public function runPhpUnit(TableNode $options): void
    {
        $this->executePhpUnit(
            $options->getColumn(0),
        );
    }

    #[Then('the effective PHPUnit configuration includes:')]
    public function assertConfiguration(TableNode $settings): void
    {
        $configuration = $this->scenarioState->getLastPhpUnitExecutionResult()->configuration;

        foreach ($settings->getRows() as [$name, $value]) {
            Assert::keyExists(
                $configuration,
                $name,
                sprintf(
                    'PHPUnit did not record the setting "%s".',
                    $name,
                ),
            );
            Assert::same(
                $configuration[$name],
                Json::decode($value),
                sprintf(
                    'Unexpected PHPUnit setting "%s".',
                    $name,
                ),
            );
        }
    }

    #[Then('PHPUnit output contains :message')]
    public function assertOutputContains(string $message): void
    {
        Assert::contains(
            $this->scenarioState->getLastPhpUnitExecutionResult()->output,
            $message,
            sprintf(
                'PHPUnit did not explain "%s".',
                $message,
            ),
        );
    }

    #[Given('a successful PHPUnit run has executed all tests and recorded their impact data')]
    public function runAllTestsAndRecordImpactData(): void
    {
        $this->executePhpUnit([]);

        $this->assertAllTestsWereExecuted();

        Assert::fileExists(
            $this->scenarioState->scenarioProjectDirectory
                . '/'
                . $this->scenarioState->getLastPhpUnitExecutionResult()->configuration['testImpactDataFile'],
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

    #[Then('the initial test run executes only the following tests:')]
    public function assertOnlyListedTestsWereExecuted(TableNode $tests): void
    {
        $this->assertExecutedTests(
            array_map(
                self::qualifyTestName(...),
                $tests->getColumn(0),
            ),
        );
    }

    #[Then('the initial test run executes all tests')]
    public function assertAllTestsWereExecuted(): void
    {
        $this->assertExecutedTests(
            $this->scenarioState->getLastPhpUnitExecutionResult()->loadedTests,
        );
    }

    #[Then('the initial test run executes no tests')]
    public function assertNoTestsWereExecuted(): void
    {
        $this->assertExecutedTests([]);
    }

    #[Given('the recorded impact data becomes :state')]
    public function invalidateRecording(string $state): void
    {
        $path = $this->getRecordingPath();

        Assert::fileExists(
            $path,
            'Expected an existing impact recording before invalidating it.',
        );

        if ($state === 'missing') {
            $this->filesystem->remove($path);

            return;
        }

        if ($state === 'empty') {
            $this->filesystem->dumpFile(
                $path,
                '',
            );

            return;
        }

        Assert::same(
            $state,
            'incompatible',
            'Unknown impact-recording state.',
        );

        $data = Json::decode(
            $this->filesystem->readFile($path),
        );
        $data['phpunit'] = 'incompatible-build';

        $this->filesystem->dumpFile(
            $path,
            json_encode(
                $data,
                JSON_THROW_ON_ERROR,
            ),
        );
    }

    #[Given('I change the shared execution dependency :dependency')]
    public function changeDependency(string $dependency): void
    {
        $project = $this->scenarioState->scenarioProjectDirectory;

        if ($dependency === 'bootstrap script') {
            $this->filesystem->appendToFile(
                $project . '/vendor/autoload.php',
                "\n// Changed after recording impact data.\n",
            );

            return;
        }

        if ($dependency === 'composer.lock') {
            // A whitespace-only edit changes the hash without invalidating the installed packages.
            $this->filesystem->appendToFile(
                $project . '/composer.lock',
                "\n",
            );

            return;
        }

        Assert::same(
            $dependency,
            'PHPUnit XML setting',
            'Unknown shared execution dependency.',
        );

        $this->copyPhpUnitConfiguration(
            __DIR__.'/../../configurations/tia-observed-backup-globals.xml',
        );
    }

    #[Given('Infection writes generated PHPUnit XML outside the scenario project')]
    public function useExternalTemporaryDirectory(): void
    {
        $path = $this->scenarioState->scenarioProjectDirectory . '/infection.json5';
        $configuration = Json::decode(
            $this->filesystem->readFile($path),
        );
        $configuration['tmpDir'] = Path::join(
            $this->scenarioState->rootDirectory,
            'var/behat/external',
            basename($this->scenarioState->scenarioProjectDirectory),
        );

        $this->filesystem->remove($configuration['tmpDir']);
        $this->filesystem->dumpFile(
            $path,
            json_encode(
                $configuration,
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT,
            ),
        );
    }

    #[Given('the impact cache is obstructed by :obstruction')]
    public function obstructCache(string $obstruction): void
    {
        $recording = $this->getRecordingPath();

        if ($obstruction === 'a file at the cache-directory path') {
            $directory = dirname($recording);

            $this->filesystem->remove($directory);
            $this->filesystem->dumpFile(
                $directory,
                'This file prevents creating the cache directory.',
            );

            return;
        }

        Assert::same(
            $obstruction,
            'a directory at the recording-file path',
            'Unknown cache obstruction.',
        );

        $this->filesystem->remove($recording);
        $this->filesystem->mkdir($recording);
    }

    private function getRecordingPath(): string
    {
        $recording = $this->scenarioState->getLastPhpUnitExecutionResult()->configuration['testImpactDataFile'];

        Assert::stringNotEmpty(
            $recording,
            'The previous PHPUnit run did not configure an impact recording path.',
        );

        return Path::makeAbsolute(
            $recording,
            $this->scenarioState->scenarioProjectDirectory,
        );
    }

    /**
     * @param string $testName E.g. "CalculatorTest"
     *
     * @return string FQCN
     */
    private static function qualifyTestName(string $testName): string
    {
        return sprintf(
            "Infection\\E2ETests\\PHPUnitTIA\\Tests\\%s",
            $testName,
        );
    }

    /**
     * @param list<string> $options
     */
    private function executePhpUnit(array $options): void
    {
        $logPath = $this->scenarioState->scenarioProjectDirectory . '/' . self::PHPUNIT_OUTPUT_PATH;
        $this->filesystem->dumpFile(
            $logPath,
            '',
        );

        $this->shellCommandRunner->mustRun(
            [
                PHP_BINARY,
                'vendor/bin/phpunit',
                '--configuration',
                'phpunit.xml',
                ...$options,
            ],
            callback: function (string $type, string $output) use ($logPath): void {
                $this->filesystem->appendToFile(
                    $logPath,
                    $output,
                );
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
        $actual = self::sortTests(
            $this->scenarioState->getLastPhpUnitExecutionResult()->executedTests,
        );

        Assert::same(
            $actual,
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
