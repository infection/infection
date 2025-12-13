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

namespace Infection\TestFramework\PhpUnit;

use function array_key_exists;
use Generator;
use Infection\TestFramework\Coverage\Trace;
use Infection\TestFramework\Coverage\TraceProvider;
use Infection\TestFramework\Tracing\Tracer;
use SplFileInfo;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class PhpUnitTracer implements Tracer
{
    private bool $traversed = false;

    private ?Generator $traceGenerator = null;

    /**
     * @var array<string, Trace|null> Traces indexed by their source pathname.
     */
    private array $indexedTraces = [];

    public function __construct(
        private readonly TraceProvider $traceProvider,
    ) {
    }

    public function hasTrace(SplFileInfo $fileInfo): bool
    {
        return $this->tryToTrace($fileInfo) !== null;
    }

    public function trace(SplFileInfo $fileInfo): Trace
    {
        $trace = $this->tryToTrace($fileInfo);

        Assert::notNull($trace);

        return $trace;
    }

    private function tryToTrace(SplFileInfo $fileInfo): ?Trace
    {
        $sourcePathname = $fileInfo->getPathname();

        return array_key_exists($sourcePathname, $this->indexedTraces)
            ? $this->indexedTraces[$sourcePathname]
            : $this->lookup($sourcePathname);
    }

    private function lookup(string $sourcePathname): ?Trace
    {
        if ($this->traversed) {
            // We already got all the traces, yet it was not found.
            // Cache the fact that no trace exists for that source file.
            $this->indexedTraces[$sourcePathname] = null;

            return null;
        }

        $traces = $this->getTraceGenerator();

        // Do not use a foreach loop as it does a rewind which we do not want
        // to do.
        while ($traces->valid()) {
            /** @var Trace $trace */
            $trace = $traces->current();
            $traces->next();

            $traceSourcePathname = $trace->getSourceFileInfo()->getPathname();
            $this->indexedTraces[$traceSourcePathname] = $trace;

            if ($traceSourcePathname === $sourcePathname) {
                return $trace;
            }

            if ($this->traversed) {
                break;
            }
        }

        $this->indexedTraces[$sourcePathname] = null;

        return null;
    }

    /**
     * @return Generator<Trace>
     */
    private function getTraceGenerator(): Generator
    {
        if ($this->traceGenerator === null) {
            $this->traceGenerator = $this->createTraceGenerator();
        }

        return $this->traceGenerator;
    }

    /**
     * @return Generator<Trace>
     */
    private function createTraceGenerator(): Generator
    {
        foreach ($this->traceProvider->provideTraces() as $trace) {
            yield $trace;
        }

        $this->traversed = true;
    }
}
