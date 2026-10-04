<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\PHPUnit;

use function array_map;
use function getenv;
use function dirname;
use function getcwd;
use function json_encode;
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
    #[Override]
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        if (!$this->isInMutantProcess()) {
            $filesystem = new Filesystem();
            $this->recordConfiguration($configuration, $parameters->get('configurationFilePath'), $filesystem);

            $facade->registerSubscriber(
                new RecordInitialTestsSubscriber($parameters->get('executedTestsFilePath'), $filesystem),
            );
            $facade->registerSubscriber(
                new RecordLoadedTestsSubscriber(
                    $parameters->get('loadedTestsFilePath'),
                    $filesystem,
                ),
            );
        }
    }

    private function recordConfiguration(Configuration $configuration, string $filePath, Filesystem $filesystem): void
    {
        // Impact selection options are CLI-only and absent from the merged configuration.
        $cli = (new Builder(EventFacade::emitter()))->fromParameters($_SERVER['argv']);
        $cacheDirectory = $configuration->hasCacheDirectory() ? $configuration->cacheDirectory() : null;

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
            'onlyImpacted' => $cli->onlyImpacted(),
            'impactedBy' => $cli->hasImpactedBy()
                ? array_map($this->relativeToFixture(...), $cli->impactedBy())
                : [],
            'impactedByFile' => $cli->hasImpactedByFile() ? $this->relativeToFixture($cli->impactedByFile()) : null,
            'cacheTestIndex' => $configuration->cacheTestIndex(),
            'recordTestRunHistory' => $configuration->recordTestRunHistory(),
            'cacheDirectory' => $this->relativeToFixture($cacheDirectory),
            'coverageCacheDirectory' => $this->relativeToFixture(
                $configuration->hasCoverageCacheDirectory() ? $configuration->coverageCacheDirectory() : null,
            ),
            'testRunHistoryFile' => $this->relativeToFixture($configuration->testRunHistoryFile()),
            // PHPUnit's TestImpactDataFile uses this filename inside the cache directory.
            'testImpactDataFile' => $cacheDirectory === null ? null : $this->relativeToFixture($cacheDirectory . '/test-impact-data'),
        ];

        $filesystem->dumpFile($filePath, json_encode($record, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n");
    }

    private function relativeToFixture(?string $path): ?string
    {
        if ($path === null || $path === '-') {
            return $path;
        }

        $workingDirectory = getcwd();
        Assert::stringNotEmpty(
            $workingDirectory,
            'Cannot record PHPUnit configuration paths because the working directory could not be determined.',
        );

        return Path::makeRelative(Path::makeAbsolute($path, $workingDirectory), dirname(__DIR__));
    }

    private function isInMutantProcess(): bool
    {
        return getenv('TEST_TOKEN') !== false;
    }
}
