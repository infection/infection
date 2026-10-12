<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Behat;

use Webmozart\Assert\Assert;
use function array_key_last;
use function dirname;

/**
 * Holds the mutable project and execution data shared by a scenario's contexts.
 */
final class ScenarioState
{
    public readonly string $rootDirectory;
    public string $scenarioProjectDirectory;

    /**
     * @var list<InfectionExecutionResult>
     */
    public array $infectionExecutionResults = [];

    /**
     * @var list<PhpUnitExecutionResult>
     */
    public array $phpUnitExecutionResults = [];

    public function __construct()
    {
        $this->rootDirectory = dirname(__DIR__, 2);
    }

    public function reset(): void
    {
        unset($this->scenarioProjectDirectory);
        $this->phpUnitExecutionResults = [];
        $this->infectionExecutionResults = [];
    }

    public function findLastPhpUnitExecutionResult(): ?PhpUnitExecutionResult
    {
        return $this->phpUnitExecutionResults[array_key_last($this->phpUnitExecutionResults)] ?? null;
    }

    public function getLastPhpUnitExecutionResult(): PhpUnitExecutionResult
    {
        $result = $this->findLastPhpUnitExecutionResult();

        Assert::notNull($result, 'PHPUnit has not been run in this scenario project.');

        return $result;
    }

    public function findLastInfectionExecutionResult(): ?InfectionExecutionResult
    {
        return $this->infectionExecutionResults[array_key_last($this->infectionExecutionResults)] ?? null;
    }

    public function getLastInfectionExecutionResult(): InfectionExecutionResult
    {
        $result = $this->findLastInfectionExecutionResult();

        Assert::notNull($result, 'Infection has not been run in this scenario project.');

        return $result;
    }

    public function getFirstInfectionExecutionResult(): InfectionExecutionResult
    {
        $result = $this->infectionExecutionResults[0] ?? null;

        Assert::notNull($result, 'Infection has not been run in this scenario project.');

        return $result;
    }
}
