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
use function function_exists;
use Infection\Container;
use Infection\TestFramework\Coverage\Trace;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\NullOutput;

require_once __DIR__ . '/../../../vendor/autoload.php';

if (!function_exists('Infection\Benchmark\Tracing\fetchTraceLazyState')) {
    function fetchTraceLazyState(Trace $trace): void
    {
        $trace->getSourceFileInfo();
        $trace->hasTests();
        $trace->getTests();
    }
}

/**
 * @param positive-int $maxCount
 *
 * @return Closure():(positive-int|0)
 */
return static function (int $maxCount): Closure {
    $container = Container::create()->withValues(
        logger: new NullLogger(),
        output: new NullOutput(),
        configFile: __DIR__ . '/infection.json5',
        existingCoveragePath: __DIR__ . '/coverage',
        useNoopMutators: true,
    );
    $traceProvider = $container->getUnionTraceProvider();

    return static function () use ($maxCount, $traceProvider) {
        $count = 0;

        foreach ($traceProvider->provideTraces() as $trace) {
            fetchTraceLazyState($trace);

            ++$count;

            if ($count >= $maxCount) {
                break;
            }
        }

        return $count;
    };
};
