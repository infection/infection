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

use function array_map;
use function array_splice;
use Closure;
use function count;
use function function_exists;
use Infection\Container\Container;
use Infection\TestFramework\Tracing\Trace\EmptyTrace;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\TestFramework\Tracing\TraceProvider;
use Infection\TestFramework\Tracing\Tracer;
use function iterator_to_array;
use function max;
use function min;
use Psr\Log\NullLogger;
use function round;
use SplFileInfo;
use function sprintf;
use Symfony\Component\Console\Output\NullOutput;
use Webmozart\Assert\Assert;

require_once __DIR__ . '/../../../vendor/autoload.php';

if (!function_exists('Infection\Benchmark\Tracing\fetchTraceLazyState')) {
    function fetchTraceLazyState(Trace $trace): void
    {
        $trace->getSourceFileInfo();
        $trace->hasTests();
        $trace->getTests();
    }
}

if (!function_exists('Infection\Benchmark\Tracing\createContainer')) {
    function createContainer(): Container
    {
        return Container::create()->withValues(
            logger: new NullLogger(),
            output: new NullOutput(),
            configFile: __DIR__ . '/infection.json5',
            existingCoveragePath: __DIR__ . '/coverage',
            useNoopMutators: true,
        );
    }
}

if (!function_exists('Infection\Benchmark\Tracing\collectSources')) {
    /**
     * @return SplFileInfo[]
     */
    function collectSources(): array
    {
        // We need to use a fresh container instance, otherwise our lovely iterators are going to be consumed...
        $traceProvider = createContainer()->get(TraceProvider::class);

        return array_map(
            static fn (Trace $trace) => $trace->getSourceFileInfo(),
            iterator_to_array(
                $traceProvider->provideTraces(),
                preserve_keys: false,
            ),
        );
    }
}

if (!function_exists('Infection\Benchmark\Tracing\takePercentageOfSources')) {
    /**
     * @param SplFileInfo[] $sources
     *
     * @return SplFileInfo[]
     */
    function takePercentageOfSources(float $percentage, array $sources): array
    {
        $sourcesOffset = (int) max(
            0,
            min(
                count($sources),
                round(
                    count($sources) * $percentage,
                ),
            ),
        );

        return array_splice($sources, 0, $sourcesOffset);
    }
}

/**
 * @param positive-int $maxCount
 *
 * @return Closure():(positive-int|0)
 */
return static function (int $maxCount, float $percentage = 1.): Closure {
    $tracer = createContainer()->get(Tracer::class);
    $sources = collectSources();

    return static function (?float $dynamicPercentage = null) use ($maxCount, $tracer, $sources, $percentage) {
        $percentage = $dynamicPercentage ?? $percentage;
        Assert::range(
            $percentage,
            0,
            1,
            sprintf(
                'Expected the percentage to be an element of [0., 1.]. Got "%s".',
                $percentage,
            ),
        );

        $sourcesSubset = takePercentageOfSources($percentage, $sources);

        $count = 0;

        foreach ($sourcesSubset as $source) {
            $trace = $tracer->trace($source);

            if ($trace instanceof EmptyTrace) {
                continue;
            }

            fetchTraceLazyState($trace);

            ++$count;

            if ($count >= $maxCount) {
                break;
            }
        }

        return $count;
    };
};
