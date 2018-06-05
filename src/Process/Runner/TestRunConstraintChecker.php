<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process\Runner;

use Infection\Mutant\MetricsCalculator;

/**
 * @internal
 */
final class TestRunConstraintChecker
{
    const MSI_FAILURE = 'min-msi';

    const COVERED_MSI_FAILURE = 'min-covered-msi';

    /**
     * @var MetricsCalculator
     */
    private $metricsCalculator;

    /**
     * @var bool
     */
    private $ignoreMsiWithNoMutations;

    /**
     * @var float
     */
    private $minMsi;

    /**
     * @var float
     */
    private $minCoveredMsi;

    /**
     * @var string
     */
    private $failureType = '';

    public function __construct(
        MetricsCalculator $metricsCalculator,
        bool $ignoreMsiWithNoMutations,
        float $minMsi,
        float $minCoveredMsi
    ) {
        $this->metricsCalculator = $metricsCalculator;
        $this->ignoreMsiWithNoMutations = $ignoreMsiWithNoMutations;
        $this->minMsi = $minMsi;
        $this->minCoveredMsi = $minCoveredMsi;
    }

    public function hasTestRunPassedConstraints(): bool
    {
        if ($this->ignoreMsiWithNoMutations && $this->metricsCalculator->getTotalMutantsCount() === 0) {
            return true;
        }

        if ($this->hasBadMsi($this->metricsCalculator)) {
            $this->failureType = self::MSI_FAILURE;

            return false;
        }

        if ($this->hasBadCoveredMsi($this->metricsCalculator)) {
            $this->failureType = self::COVERED_MSI_FAILURE;

            return false;
        }

        return true;
    }

    public function getErrorType(): string
    {
        return $this->failureType;
    }

    public function getMinRequiredValue(): float
    {
        return $this->failureType === self::MSI_FAILURE ? $this->minMsi : $this->minCoveredMsi;
    }

    private function hasBadMsi(MetricsCalculator $metricsCalculator): bool
    {
        return $this->minMsi && ($metricsCalculator->getMutationScoreIndicator() < $this->minMsi);
    }

    private function hasBadCoveredMsi(MetricsCalculator $metricsCalculator): bool
    {
        return $this->minCoveredMsi && ($metricsCalculator->getCoveredCodeMutationScoreIndicator() < $this->minCoveredMsi);
    }
}
