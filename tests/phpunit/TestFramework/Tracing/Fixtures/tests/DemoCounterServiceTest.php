<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\Tracing\Fixtures\tests;

use Infection\Tests\TestFramework\Tracing\Fixtures\src\DemoCounterService;
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
        $this->assertSame(0, $this->service->get());
    }

    public function test_count_increments_by_step_and_returns_new_value(): void
    {
        $result = $this->service->count();

        $this->assertSame(1, $result);
        $this->assertSame(1, $this->service->get());
    }

    public function test_multiple_counts_increment_correctly(): void
    {
        $result1 = $this->service->count();
        $result2 = $this->service->count();
        $result3 = $this->service->count();

        $this->assertSame(1, $result1);
        $this->assertSame(2, $result2);
        $this->assertSame(3, $result3);
        $this->assertSame(3, $this->service->get());
    }

    public function test_start_count_sets_initial_value(): void
    {
        $this->service->startCount(5);

        $this->assertSame(5, $this->service->get());
    }

    public function test_start_count_with_default_sets_to_zero(): void
    {
        $this->service->count();
        $this->service->startCount();

        $this->assertSame(0, $this->service->get());
    }

    public function test_start_count_affects_subsequent_counts(): void
    {
        $this->service->startCount(10);
        $result = $this->service->count();

        $this->assertSame(11, $result);
        $this->assertSame(11, $this->service->get());
    }

    public function test_set_step_changes_increment_amount(): void
    {
        $this->service->setStep(5);
        $result = $this->service->count();

        $this->assertSame(5, $result);
        $this->assertSame(5, $this->service->get());
    }

    public function test_set_step_with_default_resets_to_one(): void
    {
        $this->service->setStep(3);
        $this->service->setStep();
        $result = $this->service->count();

        $this->assertSame(1, $result);
    }

    public function test_custom_step_with_multiple_counts(): void
    {
        $this->service->setStep(3);

        $result1 = $this->service->count();
        $result2 = $this->service->count();

        $this->assertSame(3, $result1);
        $this->assertSame(6, $result2);
        $this->assertSame(6, $this->service->get());
    }

    public function test_negative_step_decreases_counter(): void
    {
        $this->service->startCount(10);
        $this->service->setStep(-2);

        $result = $this->service->count();

        $this->assertSame(8, $result);
        $this->assertSame(8, $this->service->get());
    }

    public function test_zero_step_keeps_counter_unchanged(): void
    {
        $this->service->startCount(5);
        $this->service->setStep(0);

        $result = $this->service->count();

        $this->assertSame(5, $result);
        $this->assertSame(5, $this->service->get());
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

        $this->assertSame(110, $result1);
        $this->assertSame(105, $result2);
        $this->assertSame(45, $result3);
        $this->assertSame(45, $this->service->get());
    }
}
