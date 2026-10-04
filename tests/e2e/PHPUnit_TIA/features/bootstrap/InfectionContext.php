<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Step\Then;
use Behat\Step\When;
use DOMDocument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;
use function array_combine;
use function array_column;
use function array_any;
use function array_keys;
use function array_filter;
use function array_map;
use function array_values;
use function count;
use function dirname;
use function sort;
use function sprintf;
use function str_starts_with;

final class InfectionContext implements Context
{
    private const string PHPUNIT_CONFIGURATION_RECORDING_PATH = 'var/phpunit/configuration.json';
    private const string PHPUNIT_LOADED_TESTS_RECORDING_PATH = 'var/phpunit/loaded-tests.json';
    private const string PHPUNIT_EXECUTED_TESTS_RECORDING_PATH = 'var/phpunit/executed-tests.jsonl';
    private const string EXECUTION_REPORT_PATH = 'var/infection/execution.jsonl';
    private const string INFECTION_REPORT_PATH = 'var/infection/infection.json';
    private const string OUTPUT_PATH_FORMAT = 'var/infection/output-%d.log';

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly ShellCommandRunner $shellCommandRunner,
        private readonly ScenarioState $scenarioState,
    ) {
    }

    #[When('I run Infection on :source')]
    #[When('I run Infection on :source with the following options:')]
    public function runInfection(string $source, ?TableNode $options = null): void
    {
        $this->executeInfection([
            PHP_BINARY,
            $this->getInfectionBin(),
            $source,
            // Escaped mutants are expected in development-cycle scenarios; assert their outcomes below.
            '--min-msi=0',
            '--debug',
            '--no-progress',
            '--no-interaction',
            ...($options?->getColumn(0) ?? []),
        ]);
    }

    #[Then('Infection output contains :message')]
    public function assertOutputContains(string $message): void
    {
        Assert::contains(
            $this->scenarioState->getLastInfectionExecutionResult()->output,
            $message,
            sprintf(
                'Infection did not explain "%s".',
                $message,
            ),
        );
    }

    #[When('I run Infection again with the same options and unchanged source, tests, and configuration')]
    public function runInfectionAgainWithSameOptionsAndUnchangedProject(): void
    {
        $this->executeInfection(
            $this->scenarioState->getLastInfectionExecutionResult()->command,
        );
    }

    #[Then('the mutants that were evaluated are:')]
    public function assertEvaluatedMutantsAre(TableNode $mutants): void
    {
        $expected = $mutants->getHash();
        $actual = array_map(
            fn (array $mutant): array => [
                'file' => Path::makeRelative(
                    $mutant['file'],
                    $this->scenarioState->scenarioProjectDirectory,
                ),
                'mutator' => $mutant['mutator'],
                'outcome' => $mutant['status'],
            ],
            $this->scenarioState->getLastInfectionExecutionResult()->getMutations(),
        );

        sort($actual);
        sort($expected);

        Assert::same(
            $actual,
            $expected,
            'The evaluated mutants and their outcomes do not match the listed mutants and outcomes.',
        );
    }

    #[Then('no mutations are generated or evaluated')]
    public function assertNoMutations(): void
    {
        $result = $this->scenarioState->getLastInfectionExecutionResult();

        Assert::isEmpty(
            $result->getMutations(),
            'An empty initial selection must not evaluate mutants.',
        );
        Assert::allIsEmpty(
            array_column(
                $result->getSources(),
                'mutationHashes',
            ),
            'An empty initial selection must not generate mutants.',
        );
    }

    #[Then('line-to-test coverage for :source is unchanged from the first Infection run')]
    public function assertLineToTestCoverageIsUnchangedFromFirstRun(string $source): void
    {
        $sourcePath = Path::join(
            $this->scenarioState->scenarioProjectDirectory,
            $source,
        );

        $getCoverage = static function (InfectionExecutionResult $result) use ($sourcePath): array {
            $coverageBySource = array_column(
                $result->getSources(),
                'coverage',
                'file',
            );

            Assert::keyExists(
                $coverageBySource,
                $sourcePath,
                sprintf(
                    'The Infection run did not report source file "%s".',
                    $sourcePath,
                ),
            );

            return $coverageBySource[$sourcePath];
        };

        $actual = $getCoverage(
            $this->scenarioState->getLastInfectionExecutionResult(),
        );
        $expected = $getCoverage(
            $this->scenarioState->getFirstInfectionExecutionResult(),
        );

        Assert::notEmpty(
            $actual,
            sprintf(
                'The latest Infection run did not report any line-to-test coverage for "%s".',
                $source,
            ),
        );
        Assert::same(
            $actual,
            $expected,
            sprintf(
                'Line-to-test coverage for "%s" changed since the first Infection run.',
                $source,
            ),
        );
    }

    #[Then('the generated mutations and their detection statuses are unchanged from the first Infection run')]
    public function assertGeneratedMutationsAndDetectionStatusesAreUnchanged(): void
    {
        Assert::same(
            array_column(
                $this->scenarioState->getLastInfectionExecutionResult()->getSources(),
                'mutationHashes',
            ),
            array_column(
                $this->scenarioState->getFirstInfectionExecutionResult()->getSources(),
                'mutationHashes',
            ),
            'The generated mutation hashes differ from the first Infection run.',
        );

        Assert::same(
            $this->scenarioState->getLastInfectionExecutionResult()->getMutations(),
            $this->scenarioState->getFirstInfectionExecutionResult()->getMutations(),
            'The evaluated mutations or their detection statuses differ from the first Infection run.',
        );
    }

    #[Then('the reported MSI is unchanged from the first Infection run')]
    public function assertReportedMsiIsUnchanged(): void
    {
        Assert::same(
            $this->scenarioState->getLastInfectionExecutionResult()->getMsi(),
            $this->scenarioState->getFirstInfectionExecutionResult()->getMsi(),
            'The reported MSI differs from the first Infection run.',
        );
    }

    #[Then('coverage for :source contains exactly these lines:')]
    public function assertCoveredLines(string $source, TableNode $lines): void
    {
        $sourcePath = Path::join(
            $this->scenarioState->scenarioProjectDirectory,
            $source,
        );
        $matchesSource = static fn (array $record): bool => $record['file'] === $sourcePath;
        $sources = array_values(
            array_filter(
                $this->scenarioState->getLastInfectionExecutionResult()->getSources(),
                $matchesSource,
            ),
        );

        Assert::count(
            $sources,
            1,
            sprintf(
                'Expected one processed source record for "%s".',
                $source,
            ),
        );

        $actual = array_column(
            $sources[0]['coverage'],
            'line',
        );
        $expected = array_map(
            static fn (string $line): int => (int) $line,
            $lines->getColumn(0),
        );

        sort($actual);
        sort($expected);

        Assert::same(
            $actual,
            $expected,
            sprintf(
                'Unexpected covered lines for "%s".',
                $source,
            ),
        );
    }

    #[Then('the results match a run with TIA disabled:')]
    public function assertResultsMatchWithoutTia(TableNode $results): void
    {
        $comparisons = [
            'coverage' => static fn (InfectionExecutionResult $result): array => array_column(
                $result->getSources(),
                'coverage',
                'file',
            ),
            'generated mutations' => static fn (InfectionExecutionResult $result): array => array_column(
                $result->getSources(),
                'mutationHashes',
                'file',
            ),
            'detection statuses' => static fn (InfectionExecutionResult $result): array => $result->getMutations(),
            'MSI' => static fn (InfectionExecutionResult $result): float => $result->getMsi(),
        ];
        $requestedResults = $results->getColumn(0);

        Assert::notEmpty(
            $requestedResults,
            'The TIA-disabled comparison must specify results to compare.',
        );
        Assert::allOneOf(
            $requestedResults,
            array_keys($comparisons),
            'Unknown result requested for the TIA-disabled comparison: %s. Expected one of: %2$s.',
        );

        $previousRun = $this->scenarioState->getLastInfectionExecutionResult();

        $withoutTia = $this->executeInfectionWithoutTia($previousRun->command);

        $selectResults = static fn (InfectionExecutionResult $result): array => array_combine(
            $requestedResults,
            array_map(
                static fn (string $name): array|float => $comparisons[$name]($result),
                $requestedResults,
            ),
        );

        Assert::same(
            $selectResults($previousRun),
            $selectResults($withoutTia),
            'The previous run and the run with TIA disabled differ in the requested results.',
        );
    }

    #[Then('each mutant is tested using exactly these tests:')]
    public function assertEachMutantIsTestedUsingExactlyTheseTests(TableNode $tests): void
    {
        $expectedTests = array_map(
            static fn (string $test): string => 'Infection\\E2ETests\\PHPUnitTIA\\Tests\\' . $test,
            $tests->getColumn(0),
        );
        sort($expectedTests);

        foreach ($this->getEvaluatedMutants() as $mutant) {
            $phpunit = $this->readMutantPhpUnitExecutionResult($mutant);

            $loadedTests = $phpunit->loadedTests;
            $executedTests = $phpunit->executedTests;

            sort($loadedTests);
            sort($executedTests);

            Assert::same(
                $loadedTests,
                $expectedTests,
                sprintf(
                    'Mutant "%s" must load exactly the listed tests.',
                    $mutant['hash'],
                ),
            );
            Assert::same(
                $executedTests,
                $expectedTests,
                sprintf(
                    'Mutant "%s" must execute exactly the listed tests.',
                    $mutant['hash'],
                ),
            );
        }
    }

    #[Then('TIA selection and recording are disabled for mutant test runs')]
    public function assertTiaIsDisabledForMutants(): void
    {
        $tiaAttributes = [
            'recordTestImpactData',
            'deriveTestImpactDataFromCoverageTargets',
        ];

        $tiaOptions = [
            '--impacted-by',
            '--only-impacted',
            '--record-test-impact-data',
            '--derive-test-impact-data-from-coverage-targets',
        ];

        foreach ($this->getEvaluatedMutants() as $mutant) {
            $document = new DOMDocument();
            $document->loadXML($mutant['configuration']);

            foreach ($tiaAttributes as $attribute) {
                Assert::notInArray(
                    $document->documentElement->getAttribute($attribute),
                    ['true', '1'],
                    sprintf(
                        'Mutant XML enables "%s".',
                        $attribute,
                    ),
                );
            }

            foreach ($tiaOptions as $option) {
                Assert::notContains(
                    $mutant['commandLine'],
                    $option,
                    sprintf(
                        'Mutant command enables "%s".',
                        $option,
                    ),
                );
            }

            Assert::notContains(
                $mutant['output'],
                'Impact:',
                'The mutant process reported TIA selection.',
            );
        }
    }

    #[Then('mutants leave the initial impact data and test-run history unchanged')]
    public function assertSharedCacheIsUnchanged(): void
    {
        $result = $this->scenarioState->getLastInfectionExecutionResult();
        $initialEvents = $result->getEvents('initial_tests_finished');
        $finalEvents = $result->getEvents('mutation_testing_finished');

        Assert::count(
            $initialEvents,
            1,
            'Expected one completed initial run.',
        );
        Assert::count(
            $finalEvents,
            1,
            'Expected one completed mutation run.',
        );

        $initialCache = $initialEvents[0]['configuredCache'];

        Assert::notNull(
            $initialCache['test-impact-data'],
            'The initial run must record impact data before checking mutant isolation.',
        );
        Assert::notNull(
            $initialCache['test-run-history'],
            'The initial run must record test history before checking mutant isolation.',
        );
        Assert::same(
            $finalEvents[0]['configuredCache'],
            $initialCache,
            'Mutants changed the initial run\'s shared cache.',
        );
    }

    /**
     * @param array<string, mixed> $mutant
     */
    private function readMutantPhpUnitExecutionResult(array $mutant): PhpUnitExecutionResult
    {
        $recordingDirectory = Path::join(
            $this->scenarioState->scenarioProjectDirectory,
            'var/phpunit/mutants',
            $mutant['hash'],
        );

        return PhpUnitExecutionResult::fromRecordings(
            output: $mutant['output'],
            configurationJson: $this->filesystem->readFile(
                $recordingDirectory . '/configuration.json',
            ),
            loadedTestsJson: $this->filesystem->readFile(
                $recordingDirectory . '/loaded-tests.json',
            ),
            executedTestsJsonLines: $this->filesystem->readFile(
                $recordingDirectory . '/executed-tests.jsonl',
            ),
        );
    }

    /**
     * @return non-empty-list<array<string, mixed>>
     */
    private function getEvaluatedMutants(): array
    {
        $mutants = $this->scenarioState->getLastInfectionExecutionResult()->getEvents('mutant_finished');

        Assert::notEmpty(
            $mutants,
            'The isolation check requires at least one evaluated mutant.',
        );

        return $mutants;
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
    private function executeInfectionWithoutTia(array $command): InfectionExecutionResult
    {
        // Reuse the same project to keep paths and mutation hashes comparable. Disabling
        // recording also disables Infection's automatic impact query, without refreshing it.
        $isFrameworkOptions = static fn (string $argument): bool => str_starts_with(
            $argument,
            '--test-framework-options=',
        );

        $command = array_map(
            static fn (string $argument): string => $isFrameworkOptions($argument)
                ? $argument . ' --do-not-record-test-impact-data'
                : $argument,
            $command,
        );

        $hasFrameworkOptions = array_any($command, $isFrameworkOptions);

        if (!$hasFrameworkOptions) {
            $command[] = '--test-framework-options=--do-not-record-test-impact-data';
        }

        $this->executeInfection($command);

        $phpunit = $this->scenarioState->getLastPhpUnitExecutionResult();
        $executed = $phpunit->executedTests;
        $loaded = $phpunit->loadedTests;

        sort($executed);
        sort($loaded);

        Assert::notEmpty(
            $loaded,
            'The TIA-disabled comparison must load tests.',
        );
        Assert::same(
            $executed,
            $loaded,
            'The TIA-disabled comparison must execute the full loaded suite.',
        );
        Assert::false(
            $phpunit->configuration['recordTestImpactData'],
            'The comparison must not refresh impact data.',
        );
        Assert::null(
            $phpunit->configuration['impactedByFile'],
            'The comparison must not use an explicit impact query.',
        );

        return $this->scenarioState->getLastInfectionExecutionResult();
    }

    /**
     * @param list<string> $command
     */
    private function executeInfection(array $command): void
    {
        $runNumber = count($this->scenarioState->infectionExecutionResults) + 1;
        $logPath = Path::join(
            $this->scenarioState->scenarioProjectDirectory,
            sprintf(
                self::OUTPUT_PATH_FORMAT,
                $runNumber,
            ),
        );

        $this->execute(
            $command,
            $logPath,
        );

        $result = $this->createExecutionResult(
            $command,
            $logPath,
        );
        $this->scenarioState->infectionExecutionResults[] = $result;
        $this->scenarioState->phpUnitExecutionResults[] = $this->readInitialPhpUnitExecutionResult($result);
    }

    /**
     * @param list<string> $command
     */
    private function execute(array $command, string $logPath): void
    {
        $this->filesystem->dumpFile(
            $logPath,
            '',
        );

        $this->shellCommandRunner->mustRun(
            $command,
            callback: function (string $type, string $output) use ($logPath): void {
                $this->filesystem->appendToFile(
                    $logPath,
                    $output,
                );
            },
            cwd: $this->scenarioState->scenarioProjectDirectory,
            env: ['XDEBUG_MODE' => 'coverage'],
            timeout: 20,
        );
    }

    private function getInfectionBin(): string
    {
        $infectionBin = getenv('TIA_INFECTION')
            ?: dirname(
                $this->scenarioState->rootDirectory,
                3,
            ) . '/bin/infection';

        Assert::string(
            $infectionBin,
            'The Infection executable path must be a string.',
        );

        return $infectionBin;
    }
}
