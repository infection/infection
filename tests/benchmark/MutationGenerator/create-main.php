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

namespace Infection\Benchmark\MutationGenerator;

use function class_exists;
use Closure;
use function function_exists;
use Infection\Container\Container;
use Infection\Mutation\FileMutationGenerator;
use Infection\TestFramework\Tracing\Trace\EmptyTrace;
use Infection\TestFramework\Tracing\Trace\Trace;
use Infection\TestFramework\Tracing\Tracer;
use function iterator_to_array;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

require_once __DIR__ . '/../../../vendor/autoload.php';

// Since those files are not autoloaded, we need to manually autoload them
require_once __DIR__ . '/sources/autoload.php';

if (!function_exists('Infection\Benchmark\MutationGenerator\collectSources')) {
    /**
     * @return iterable<SplFileInfo>
     */
    function collectSources(): iterable
    {
        return Finder::create()
            ->files()
            ->in(__DIR__ . '/sources')
            ->name('*.php')
        ;
    }
}

if (!class_exists(EmptyTraceTracer::class, false)) {
    final readonly class EmptyTraceTracer implements Tracer
    {
        public function trace(SplFileInfo $fileInfo): Trace
        {
            require_once $fileInfo->getRealPath();

            return new EmptyTrace($fileInfo);
        }
    }
}

/**
 * @param positive-int $maxCount
 *
 * @return Closure():(positive-int|0)
 */
return static function (int $maxCount): Closure {
    $container = Container::create();

    $sources = iterator_to_array(
        collectSources(),
        false,
    );

    $mutators = $container->getMutatorFactory()->create(
        $container->getMutatorResolver()->resolve(['@default' => true]),
        true,
    );

    $fileMutationGenerator = new FileMutationGenerator(
        $container->getFileParser(),
        $container->getNodeTraverserFactory(),
        $container->getLineRangeCalculator(),
        $container->getSourceLineMatcher(),
        new EmptyTraceTracer(),
        $container->getFileStore(),
    );

    return static function () use ($sources, $fileMutationGenerator, $mutators, $maxCount): int {
        $count = 0;

        foreach ($sources as $source) {
            $mutations = $fileMutationGenerator->generate(
                $source,
                false,
                $mutators,
            );

            foreach ($mutations as $_) {
                ++$count;

                if ($count >= $maxCount) {
                    break 2;
                }
            }
        }

        return $count;
    };
};
