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

use Generator;
use Infection\Container;
use Infection\TestFramework\Coverage\Trace;
use function iterator_to_array;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\NullOutput;

require_once __DIR__ . '/../../../vendor/autoload.php';

$container = Container::create()->withValues(
    logger: new NullLogger(),
    output: new NullOutput(),
    configFile: __DIR__ . '/cpu-core-counter/infection.json5',
    existingCoveragePath: __DIR__ . '/coverage',
    useNoopMutators: true,
);

// Instantiating the service may be expensive, and it is not what we are interested
// in profiling here.
// For instance, at the time of writing, instantiating this services instantiates
// the TestFrameworkAdapter, which launches a process to add the vendor bin to
// the paths, which turns out to be extremely expensive compared to the trace
// generation.
$traceProvider = $container->getUnionTraceProvider();

return static function () use ($traceProvider): int {
    $traces = $traceProvider->provideTraces();
    $traceCount = 0;

    foreach ($traces as $trace) {
        /** @var Trace $trace */
        // Load any potential lazy-state
        $trace->getSourceFileInfo();
        $trace->getTests();
        // Iterate over the generator: do not use iterator_to_array which is less GC friendly

        ++$traceCount;
    }

    return $traceCount;
};
