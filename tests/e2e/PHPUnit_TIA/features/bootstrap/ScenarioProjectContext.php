<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Hook\BeforeScenario;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

/**
 * Recreates an isolated scenario project before each PHPUnit TIA scenario and records
 * its location in the shared scenario state. Keeps source files, tests, and generated artefacts
 * local to the scenario while reusing the fixture's installed dependencies.
 */
final class ScenarioProjectContext implements Context
{
    // List of the directories to copy. Indeed, we do not copy the entirety of
    // the PHPUnit_TIA directory as we would otherwise end up with recursion
    // issues.
    private const array PROJECT_DIRS = [
        __DIR__.'/../../phpunit',
        __DIR__.'/../../src',
        __DIR__.'/../../tests',
        __DIR__.'/../../vendor',
    ];

    // List of the files to copy. Indeed, we do not copy the entirety of
    // the PHPUnit_TIA directory as we would otherwise end up with recursion
    // issues.
    private const array PROJECT_FILES = [
        __DIR__.'/../../composer.json',
        __DIR__.'/../../composer.lock',
        __DIR__.'/../../infection.json5',
    ];

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly ShellCommandRunner $shellCommandRunner,
        private readonly ScenarioState $scenarioState,
    ) {
    }

    #[BeforeScenario]
    public function prepareScenarioProject(BeforeScenarioScope $scope): void
    {
        $this->scenarioState->scenarioProjectDirectory = sprintf(
            '%s/var/behat/scenarios/%s',
            $this->scenarioState->rootDirectory,
            self::createScenarioDirectoryName($scope),
        );

        $this->createScenarioProject();
    }

    private static function createScenarioDirectoryName(BeforeScenarioScope $scope): string
    {
        $featureFileName = pathinfo(
            $scope->getFeature()->getFile(),
            PATHINFO_FILENAME,
        );
        $scenarioTitle = $scope->getScenario()->getTitle();

        return self::normalizeName(
            sprintf(
                '%s-%s',
                $featureFileName,
                $scenarioTitle,
            ),
        );
    }

    private static function normalizeName(string $name): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-');
    }

    private function createScenarioProject(): void
    {
        $this->filesystem->remove($this->scenarioState->scenarioProjectDirectory);
        $this->filesystem->mkdir($this->scenarioState->scenarioProjectDirectory);

        $this->copyProjectFiles();
        $this->rebuildAutoloader();
    }

    private function copyProjectFiles(): void
    {
        foreach (self::PROJECT_DIRS as $directory) {
            $this->filesystem->mirror(
                $directory,
                Path::join(
                    $this->scenarioState->scenarioProjectDirectory,
                    basename($directory),
                ),
            );
        }

        foreach (self::PROJECT_FILES as $file) {
            $this->filesystem->copy(
                $file,
                Path::join(
                    $this->scenarioState->scenarioProjectDirectory,
                    basename($file),
                ),
            );
        }
    }

    private function rebuildAutoloader(): void
    {
        $this->shellCommandRunner->mustRun(
            [
                'composer',
                'dump-autoload',
                '--no-interaction',
            ],
            cwd: $this->scenarioState->scenarioProjectDirectory,
        );
    }
}
