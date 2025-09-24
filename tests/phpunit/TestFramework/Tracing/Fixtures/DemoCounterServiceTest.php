<?php

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Tracing\Fixtures;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DemoCounterService::class)]
final class DemoCounterServiceTest extends TestCase
{
    private DemoCounterService $service;

    protected function setUp(): void
    {
        $this->service = new DemoCounterService();
    }

    public function test_initial_counter_is_zero(): void
    {
        self::assertSame(0, $this->service->get());
    }

    public function test_count_increments_by_step_and_returns_new_value(): void
    {
        $result = $this->service->count();

        self::assertSame(1, $result);
        self::assertSame(1, $this->service->get());
    }

    public function test_multiple_counts_increment_correctly(): void
    {
        $result1 = $this->service->count();
        $result2 = $this->service->count();
        $result3 = $this->service->count();

        self::assertSame(1, $result1);
        self::assertSame(2, $result2);
        self::assertSame(3, $result3);
        self::assertSame(3, $this->service->get());
    }

    public function test_start_count_sets_initial_value(): void
    {
        $this->service->startCount(5);

        self::assertSame(5, $this->service->get());
    }

    public function test_start_count_with_default_sets_to_zero(): void
    {
        $this->service->count();
        $this->service->startCount();

        self::assertSame(0, $this->service->get());
    }

    public function test_start_count_affects_subsequent_counts(): void
    {
        $this->service->startCount(10);
        $result = $this->service->count();

        self::assertSame(11, $result);
        self::assertSame(11, $this->service->get());
    }

    public function test_set_step_changes_increment_amount(): void
    {
        $this->service->setStep(5);
        $result = $this->service->count();

        self::assertSame(5, $result);
        self::assertSame(5, $this->service->get());
    }

    public function test_set_step_with_default_resets_to_one(): void
    {
        $this->service->setStep(3);
        $this->service->setStep();
        $result = $this->service->count();

        self::assertSame(1, $result);
    }

    public function test_custom_step_with_multiple_counts(): void
    {
        $this->service->setStep(3);

        $result1 = $this->service->count();
        $result2 = $this->service->count();

        self::assertSame(3, $result1);
        self::assertSame(6, $result2);
        self::assertSame(6, $this->service->get());
    }

    public function test_negative_step_decreases_counter(): void
    {
        $this->service->startCount(10);
        $this->service->setStep(-2);

        $result = $this->service->count();

        self::assertSame(8, $result);
        self::assertSame(8, $this->service->get());
    }

    public function test_zero_step_keeps_counter_unchanged(): void
    {
        $this->service->startCount(5);
        $this->service->setStep(0);

        $result = $this->service->count();

        self::assertSame(5, $result);
        self::assertSame(5, $this->service->get());
    }

    public function test_complex_scenario(): void
    {
        $this->service->startCount(100);
        $this->service->setStep(10);

        $result1 = $this->service->count();

        $this->service->setStep(-5);
        $result2 = $this->service->count();

        $this->service->startCount(50);
        $result3 = $this->service->count();

        self::assertSame(110, $result1);
        self::assertSame(105, $result2);
        self::assertSame(45, $result3);
        self::assertSame(45, $this->service->get());
    }
}