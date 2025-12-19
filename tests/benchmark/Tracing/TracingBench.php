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

namespace Infection\Benchmark\Tracing;

use Closure;
use const PHP_INT_MAX;
use PhpBench\Attributes\AfterMethods;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use Webmozart\Assert\Assert;

/**
 * To execute this test run `make benchmark_tracing`
 */
final class TracingBench
{
    private Closure $main;

    private int $count;

    public function setUp(): void
    {
        $this->main = (require __DIR__ . '/create-main.php')(PHP_INT_MAX);
    }

    public function tearDown(): void
    {
        Assert::greaterThan(
            $this->count,
            0,
            'No trace was generated.',
        );
    }

    /**
     * @param array{float} $params
     */
    #[BeforeMethods('setUp')]
    #[AfterMethods('tearDown')]
    #[Iterations(5)]
    #[ParamProviders('providePercentile')]
    public function bench(array $params): void
    {
        $percentage = (float) $params[0];

        $this->count = ($this->main)($percentage);
    }

    /**
     * @return iterable<array{float}>
     */
    public static function providePercentile(): iterable
    {
        yield '10%' => [.1];

        yield '25%' => [.25];

        yield '50%' => [.5];

        yield '75%' => [.75];

        yield '100%' => [1.];
    }
}
