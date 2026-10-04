<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\Then;
use Behat\Step\When;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;
use function array_column;
use function array_map;
use function count;
use function dirname;
use function sort;
use function sprintf;

final class InfectionContext implements Context
{
    private const string PHPUNIT_CONFIGURATION_RECORDING_PATH = 'var/phpunit/initial-configuration.json';
    private const string PHPUNIT_LOADED_TESTS_RECORDING_PATH = 'var/phpunit/initial-loaded-tests.json';
    private const string PHPUNIT_EXECUTED_TESTS_RECORDING_PATH = 'var/phpunit/initial-tests.jsonl';
    private const string EXECUTION_REPORT_PATH = 'var/infection/execution.jsonl';
    private const string INFECTION_REPORT_PATH = 'var/infection/infection.json';
    private const string OUTPUT_PATH_FORMAT = 'var/infection/output-%d.log';

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly ShellCommandRunner $shellCommandRunner,
        private readonly ScenarioState $scenarioState,
    ) {
    }

    #[When('I run Infection for :source')]
    public function runInfection(string $source): void
    {
        $this->executeInfection([
            PHP_BINARY,
            $this->getInfectionBin(),
            $source,
            '--min-msi=100',
            '--debug',
            '--no-progress',
            '--no-interaction',
        ]);
    }

    #[When('I repeat the same Infection command without changing any project files')]
    public function repeatInfection(): void
    {
        $this->executeInfection(
            $this->scenarioState->getLastInfectionExecutionResult()->command,
        );
    }

    #[Then('the following mutations are generated and evaluated:')]
    public function assertExpectedMutationsWereEvaluated(TableNode $mutations): void
    {
        $describeMutation = fn (array $mutation): array => [
            'file' => Path::makeRelative($mutation['file'], $this->scenarioState->scenarioProjectDirectory),
            'mutator' => $mutation['mutator'],
            'outcome' => $mutation['status'],
        ];

        $actual = array_map(
            $describeMutation,
            $this->scenarioState->getLastInfectionExecutionResult()->getMutations(),
        );
        $expected = $mutations->getHash();

        sort($actual);
        sort($expected);

        Assert::same(
            $actual,
            $expected,
            'The evaluated mutations and their outcomes do not match the expected mutations.',
        );
    }

    #[Then("Calculator's covering tests are unchanged from the first Infection run")]
    public function assertCalculatorCoveringTestsAreUnchanged(): void
    {
        $executionResult = $this->scenarioState->getLastInfectionExecutionResult();

        Assert::count(
            $executionResult->getSources(),
            1,
            'Expected exactly one processed source file for the Calculator coverage comparison.',
        );
        Assert::notEmpty(
            $executionResult->getSources()[0]['coverage'],
            'The initial run did not report any line-to-test coverage for Calculator.php.',
        );
        Assert::same(
            $executionResult->getSources()[0]['coverage'],
            $this->scenarioState->getFirstInfectionExecutionResult()->getSources()[0]['coverage'],
            "Calculator's line-to-test coverage changed since the first Infection run.",
        );
    }

    #[Then('the generated and evaluated mutations are identical to the first Infection run')]
    public function assertGeneratedAndEvaluatedMutationsAreUnchanged(): void
    {
        Assert::same(
            array_column($this->scenarioState->getLastInfectionExecutionResult()->getSources(), 'mutationHashes'),
            array_column($this->scenarioState->getFirstInfectionExecutionResult()->getSources(), 'mutationHashes'),
            'The generated mutation hashes differ from the first Infection run.',
        );
        Assert::same(
            $this->scenarioState->getLastInfectionExecutionResult()->getMutations(),
            $this->scenarioState->getFirstInfectionExecutionResult()->getMutations(),
            'The evaluated mutations or their detection statuses differ from the first Infection run.',
        );
    }

    #[Then('the reported MSI is identical to the first Infection run')]
    public function assertSameMsi(): void
    {
        Assert::same(
            $this->scenarioState->getLastInfectionExecutionResult()->getMsi(),
            $this->scenarioState->getFirstInfectionExecutionResult()->getMsi(),
            'The reported MSI differs from the first Infection run.',
        );
    }

    private function readInitialPhpUnitExecutionResult(InfectionExecutionResult $result): PhpUnitExecutionResult
    {
        return PhpUnitExecutionResult::fromRecordings(
            output: $result->getInitialPhpUnitOutput(),
            configurationJson: $this->filesystem->readFile(
                $this->scenarioState->scenarioProjectDirectory . '/' . self::PHPUNIT_CONFIGURATION_RECORDING_PATH,
            ),
            loadedTestsJson: $this->filesystem->readFile(
                $this->scenarioState->scenarioProjectDirectory . '/' . self::PHPUNIT_LOADED_TESTS_RECORDING_PATH,
            ),
            executedTestsJsonLines: $this->filesystem->readFile(
                $this->scenarioState->scenarioProjectDirectory . '/' . self::PHPUNIT_EXECUTED_TESTS_RECORDING_PATH,
            ),
        );
    }

    /**
     * @param list<string> $command
     */
    private function createExecutionResult(array $command, string $logPath): InfectionExecutionResult
    {
        return InfectionExecutionResult::fromRecordings(
            command: $command,
            output: $this->filesystem->readFile($logPath),
            executionReportJsonLines: $this->filesystem->readFile(
                $this->scenarioState->scenarioProjectDirectory . '/' . self::EXECUTION_REPORT_PATH,
            ),
            infectionReportJson: $this->filesystem->readFile(
                $this->scenarioState->scenarioProjectDirectory . '/' . self::INFECTION_REPORT_PATH,
            ),
        );
    }

    /**
     * @param list<string> $command
     */
    private function executeInfection(array $command): void
    {
        $runNumber = count($this->scenarioState->infectionExecutionResults) + 1;
        $logPath = Path::join(
            $this->scenarioState->scenarioProjectDirectory,
            sprintf(self::OUTPUT_PATH_FORMAT, $runNumber),
        );

        $this->execute($command, $logPath);

        $result = $this->createExecutionResult($command, $logPath);
        $this->scenarioState->infectionExecutionResults[] = $result;
        $this->scenarioState->phpUnitExecutionResults[] = $this->readInitialPhpUnitExecutionResult($result);
    }

    /**
     * @param list<string> $command
     */
    private function execute(array $command, string $logPath): void
    {
        $this->filesystem->dumpFile($logPath, '');

        $this->shellCommandRunner->mustRun(
            $command,
            callback: function (string $type, string $output) use ($logPath): void {
                $this->filesystem->appendToFile($logPath, $output);
            },
            cwd: $this->scenarioState->scenarioProjectDirectory,
            env: ['XDEBUG_MODE' => 'coverage'],
            timeout: 20,
        );
    }

    private function getInfectionBin(): string
    {
        $infectionBin = getenv('TIA_INFECTION')
            ?: dirname($this->scenarioState->rootDirectory, 3).'/bin/infection';

        Assert::string(
            $infectionBin,
            'The Infection executable path must be a string.',
        );

        return $infectionBin;
    }
}
