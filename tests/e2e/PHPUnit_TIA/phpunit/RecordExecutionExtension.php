<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\PHPUnit;

use function array_map;
use function basename;
use function getenv;
use function dirname;
use function getcwd;
use function json_encode;
use function preg_match;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const PHP_BINARY;
use Override;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\TextUI\CliArguments\Builder;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Webmozart\Assert\Assert;

final class RecordExecutionExtension implements Extension
{
    private readonly Filesystem $filesystem;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    #[Override]
    public function bootstrap(
        Configuration $configuration,
        Facade $facade,
        ParameterCollection $parameters,
    ): void {
        $mutantId = self::findMutantId($configuration);

        $recordingPaths = self::createRecordingPaths(
            $parameters,
            $mutantId,
        );

        if ($mutantId === null) {
            $this->removePreviousMutantRecordings($recordingPaths);
        }

        $this->recordConfiguration(
            $configuration,
            $recordingPaths['configuration'],
        );

        $facade->registerSubscriber(
            new RecordExecutedTestsSubscriber(
                $recordingPaths['executedTests'],
                $this->filesystem,
            ),
        );
        $facade->registerSubscriber(
            new RecordLoadedTestsSubscriber(
                $recordingPaths['loadedTests'],
                $this->filesystem,
            ),
        );
    }

    /**
     * @return array{configuration: string, executedTests: string, loadedTests: string}
     */
    private static function createRecordingPaths(
        ParameterCollection $parameters,
        ?string $mutantId,
    ): array {
        $recordingPaths = [
            'configuration' => $parameters->get('configurationFilePath'),
            'executedTests' => $parameters->get('executedTestsFilePath'),
            'loadedTests' => $parameters->get('loadedTestsFilePath'),
        ];

        return null === $mutantId
            ? $recordingPaths
            : array_map(
                static fn (string $path): string => Path::join(
                    dirname($path),
                    'mutants',
                    $mutantId,
                    basename($path),
                ),
                $recordingPaths,
            );
    }

    /**
     * @param array<string, string> $recordingPaths
     */
    private function removePreviousMutantRecordings(array $recordingPaths): void
    {
        // A mutant must provide evidence from this run, even if it previously used the same hash.
        $this->filesystem->remove(
            array_map(
                static fn (string $path): string => Path::join(
                    dirname($path),
                    'mutants',
                ),
                $recordingPaths,
            ),
        );
    }

    private function recordConfiguration(
        Configuration $configuration,
        string $filePath,
    ): void {
        // Impact selection options are CLI-only and absent from the merged configuration.
        $cliConfig = (new Builder(
            EventFacade::emitter(),
        ))->fromParameters($_SERVER['argv']);
        $cacheDirectory = $configuration->hasCacheDirectory()
            ? $configuration->cacheDirectory()
            : null;

        $record = [
            'command' => [
                PHP_BINARY,
                ...$_SERVER['argv'],
            ],
            'requireCoverageMetadata' => $configuration->requireCoverageMetadata(),
            'requireCoverageMetadataOnSmallTests' => $configuration->requireCoverageMetadataOnSmallTests(),
            'requireCoverageMetadataOnMediumTests' => $configuration->requireCoverageMetadataOnMediumTests(),
            'requireCoverageMetadataOnLargeTests' => $configuration->requireCoverageMetadataOnLargeTests(),
            'strictCoverage' => $configuration->strictCoverage(),
            'disableCoverageTargeting' => $configuration->disableCoverageTargeting(),
            'recordTestImpactData' => $configuration->recordTestImpactData(),
            'deriveTestImpactDataFromCoverageTargets' => $configuration->deriveTestImpactDataFromCoverageTargets(),
            'onlyImpacted' => $cliConfig->onlyImpacted(),
            'impactedBy' => $cliConfig->hasImpactedBy()
                ? array_map(
                    $this->makePathRelativeToRoot(...),
                    $cliConfig->impactedBy(),
                )
                : [],
            'impactedByFile' => $cliConfig->hasImpactedByFile()
                ? $this->makePathRelativeToRoot(
                    $cliConfig->impactedByFile(),
                )
                : null,
            'cacheTestIndex' => $configuration->cacheTestIndex(),
            'recordTestRunHistory' => $configuration->recordTestRunHistory(),
            'cacheDirectory' => $this->makePathRelativeToRoot($cacheDirectory),
            'coverageCacheDirectory' => $this->makePathRelativeToRoot(
                $configuration->hasCoverageCacheDirectory()
                    ? $configuration->coverageCacheDirectory()
                    : null,
            ),
            'testRunHistoryFile' => $this->makePathRelativeToRoot(
                $configuration->testRunHistoryFile(),
            ),
            // PHPUnit's TestImpactDataFile uses this filename inside the cache directory.
            'testImpactDataFile' => $cacheDirectory === null
                ? null
                : $this->makePathRelativeToRoot($cacheDirectory . '/test-impact-data'),
        ];

        $this->filesystem->dumpFile(
            $filePath,
            json_encode(
                $record,
                JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
            ) . "\n",
        );
    }

    private function makePathRelativeToRoot(?string $path): ?string
    {
        if ($path === null || $path === '-') {
            return $path;
        }

        $workingDirectory = getcwd();

        Assert::stringNotEmpty(
            $workingDirectory,
            'Cannot record PHPUnit configuration paths because the working directory could not be determined.',
        );

        return Path::makeRelative(
            Path::makeAbsolute(
                $path,
                $workingDirectory,
            ),
            dirname(__DIR__),
        );
    }

    private static function findMutantId(Configuration $configuration): ?string
    {
        if (getenv('TEST_TOKEN') === false) {
            return null;
        }

        // TODO: maybe we could pass the mutation ID as an env variable.
        // TEST_TOKEN identifies a reusable worker; the generated XML filename identifies its mutant.
        $matched = preg_match(
            '/^phpunitConfiguration\.([a-f0-9]{32})\.infection\.xml$/',
            basename(
                $configuration->configurationFile(),
            ),
            $matches,
        );

        Assert::same(
            $matched,
            1,
            'Cannot scope mutant recordings: the PHPUnit configuration filename does not contain a mutant hash.',
        );

        return $matches[1];
    }
}
