<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Behat;

use Behat\Behat\Context\Context;
use Behat\Hook\AfterScenario;

final class ScenarioStateContext implements Context
{
    public function __construct(
        private readonly ScenarioState $scenarioState,
    ) {
    }

    #[AfterScenario]
    public function resetScenarioState(): void
    {
        $this->scenarioState->reset();
    }
}
