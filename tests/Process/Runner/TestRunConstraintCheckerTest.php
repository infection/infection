<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Process\Runner;

use Infection\Mutant\MetricsCalculator;
use Infection\Process\Runner\TestRunConstraintChecker;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TestRunConstraintCheckerTest extends TestCase
{
    public function test_runs_fail_with_zero_mutations_and_no_ignore_msi_with_zero_mutations_and_required_msi(): void
    {
        $constraintChecker = new TestRunConstraintChecker(
            new MetricsCalculator(),
            false,
            10.0,
            10.0
        );

        $this->assertFalse($constraintChecker->hasTestRunPassedConstraints());
        $this->assertSame(TestRunConstraintChecker::MSI_FAILURE, $constraintChecker->getErrorType());
        $this->assertSame(10.0, $constraintChecker->getMinRequiredValue());
    }

    public function test_runs_fail_with_zero_mutations_and_no_ignore_msi_with_zero_mutations_and_required_covered_msi(): void
    {
        $constraintChecker = new TestRunConstraintChecker(
            new MetricsCalculator(),
            false,
            0.0,
            10.0
        );

        $this->assertFalse($constraintChecker->hasTestRunPassedConstraints());
        $this->assertSame(TestRunConstraintChecker::COVERED_MSI_FAILURE, $constraintChecker->getErrorType());
        $this->assertSame(10.0, $constraintChecker->getMinRequiredValue());
    }

    public function test_runs_passes_with_zero_mutations_and_no_ignore_msi_with_zero_mutations_and_no_required_msi(): void
    {
        $constraintChecker = new TestRunConstraintChecker(
            new MetricsCalculator(),
            false,
            0.0,
            0.0
        );

        $this->assertTrue($constraintChecker->hasTestRunPassedConstraints());
    }

    public function test_runs_passes_with_zero_mutations_and_ignore_msi_with_zero_mutations_and_required_msi(): void
    {
        $constraintChecker = new TestRunConstraintChecker(
            new MetricsCalculator(),
            true,
            10.0,
            0.0
        );

        $this->assertTrue($constraintChecker->hasTestRunPassedConstraints());
    }

    public function test_runs_passes_with_zero_mutations_and_ignore_msi_with_zero_mutations_option_and_required_covered_msi(): void
    {
        $constraintChecker = new TestRunConstraintChecker(
            new MetricsCalculator(),
            true,
            0.0,
            10.0
        );

        $this->assertTrue($constraintChecker->hasTestRunPassedConstraints());
    }

    public function test_run_fails_with_mutation_ignore_msi_with_zero_mutations_option_and_not_enough_msi(): void
    {
        $metrics = $this->createMock(MetricsCalculator::class);
        $metrics->expects($this->once())->method('getTotalMutantsCount')->willReturn(100);
        $metrics->expects($this->once())->method('getMutationScoreIndicator')->willReturn(8.0);
        $constraintChecker = new TestRunConstraintChecker(
            $metrics,
            true,
            10.0,
            10.0
        );

        $this->assertFalse($constraintChecker->hasTestRunPassedConstraints());
        $this->assertSame(TestRunConstraintChecker::MSI_FAILURE, $constraintChecker->getErrorType());
        $this->assertSame(10.0, $constraintChecker->getMinRequiredValue());
    }

    public function test_run_fails_with_mutation_ignore_msi_with_zero_mutations_option_and_not_enough_covered_msi(): void
    {
        $metrics = $this->createMock(MetricsCalculator::class);
        $metrics->expects($this->once())->method('getTotalMutantsCount')->willReturn(100);
        $metrics->expects($this->once())->method('getMutationScoreIndicator')->willReturn(100.0);
        $metrics->expects($this->once())->method('getCoveredCodeMutationScoreIndicator')->willReturn(8.0);
        $constraintChecker = new TestRunConstraintChecker(
            $metrics,
            true,
            10.0,
            10.0
        );

        $this->assertFalse($constraintChecker->hasTestRunPassedConstraints());
        $this->assertSame(TestRunConstraintChecker::COVERED_MSI_FAILURE, $constraintChecker->getErrorType());
        $this->assertSame(10.0, $constraintChecker->getMinRequiredValue());
    }

    public function test_run_fails_with_no_mutation_ignore_msi_with_zero_mutations_option_and_not_enough_msi(): void
    {
        $metrics = $this->createMock(MetricsCalculator::class);
        $metrics->expects($this->once())->method('getMutationScoreIndicator')->willReturn(8.0);
        $constraintChecker = new TestRunConstraintChecker(
            $metrics,
            false,
            10.0,
            10.0
        );

        $this->assertFalse($constraintChecker->hasTestRunPassedConstraints());
        $this->assertSame(TestRunConstraintChecker::MSI_FAILURE, $constraintChecker->getErrorType());
        $this->assertSame(10.0, $constraintChecker->getMinRequiredValue());
    }

    public function test_run_fails_with_no_mutation_ignore_msi_with_zero_mutations_option_and_not_enough_covered_msi(): void
    {
        $metrics = $this->createMock(MetricsCalculator::class);
        $metrics->expects($this->once())->method('getMutationScoreIndicator')->willReturn(100.0);
        $metrics->expects($this->once())->method('getCoveredCodeMutationScoreIndicator')->willReturn(8.0);
        $constraintChecker = new TestRunConstraintChecker(
            $metrics,
            false,
            10.0,
            10.0
        );

        $this->assertFalse($constraintChecker->hasTestRunPassedConstraints());
        $this->assertSame(TestRunConstraintChecker::COVERED_MSI_FAILURE, $constraintChecker->getErrorType());
        $this->assertSame(10.0, $constraintChecker->getMinRequiredValue());
    }

    public function test_run_passes_on_exactly_min_msi(): void
    {
        $metrics = $this->createMock(MetricsCalculator::class);
        $metrics->expects($this->once())->method('getMutationScoreIndicator')->willReturn(10.0);
        $metrics->expects($this->once())->method('getCoveredCodeMutationScoreIndicator')->willReturn(20.0);
        $constraintChecker = new TestRunConstraintChecker(
            $metrics,
            false,
            10.0,
            10.0
        );

        $this->assertTrue($constraintChecker->hasTestRunPassedConstraints());
    }

    public function test_run_passes_on_exactly_covered_min_msi(): void
    {
        $metrics = $this->createMock(MetricsCalculator::class);
        $metrics->expects($this->once())->method('getMutationScoreIndicator')->willReturn(20.0);
        $metrics->expects($this->once())->method('getCoveredCodeMutationScoreIndicator')->willReturn(10.0);
        $constraintChecker = new TestRunConstraintChecker(
            $metrics,
            false,
            10.0,
            10.0
        );

        $this->assertTrue($constraintChecker->hasTestRunPassedConstraints());
    }
}
