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
use function iterator_to_array;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\NullOutput;

require_once __DIR__ . '/../../../vendor/autoload.php';

$container = Container::create()->withValues(
    new NullLogger(),
    new NullOutput(),
    Container::DEFAULT_CONFIG_FILE,
    Container::DEFAULT_MUTATORS_INPUT,
    Container::DEFAULT_SHOW_MUTATIONS,
    Container::DEFAULT_LOG_VERBOSITY,
    Container::DEFAULT_DEBUG,
    Container::DEFAULT_ONLY_COVERED,
    Container::DEFAULT_FORMATTER_NAME,
    Container::DEFAULT_NO_PROGRESS,
    Container::DEFAULT_FORCE_PROGRESS,
    __DIR__ . '/coverage',
    Container::DEFAULT_INITIAL_TESTS_PHP_OPTIONS,
    Container::DEFAULT_SKIP_INITIAL_TESTS,
    Container::DEFAULT_IGNORE_MSI_WITH_NO_MUTATIONS,
    Container::DEFAULT_MIN_MSI,
    Container::DEFAULT_MIN_COVERED_MSI,
    Container::DEFAULT_MSI_PRECISION,
    Container::DEFAULT_TEST_FRAMEWORK,
    Container::DEFAULT_TEST_FRAMEWORK_EXTRA_OPTIONS,
    Container::DEFAULT_FILTER,
    Container::DEFAULT_THREAD_COUNT,
    Container::DEFAULT_DRY_RUN
);

$generateTraces = static function (?int $maxCount) use ($container): iterable {
    $traces = $container->getFilteredEnrichedTraceProvider()->provideTraces();

    if ($maxCount === null) {
        // Avoid extra limiting generator for a simpler case
        return $traces;
    }

    $i = 0;

    foreach ($traces as $trace) {
        ++$i;

        if ($i === $maxCount) {
            return;
        }

        yield $trace;
    }
};

return static function (int $maxCount) use ($generateTraces): void {
    if ($maxCount < 0) {
        $maxCount = null;
    }

    $traces = $generateTraces($maxCount);

    foreach ($traces as $_) {
        // Iterate over the generator: do not use iterator_to_array which is less GC friendly
    }
};
