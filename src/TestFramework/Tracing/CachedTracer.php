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

namespace Infection\TestFramework\Tracing;

use function array_key_exists;
use Infection\TestFramework\Tracing\Trace\Trace;
use RuntimeException;
use SplFileInfo;
use Throwable;

/**
 * Note that this implementation is not meant as a long-lived one. The goal
 * is to phase out TraceProvider.
 *
 * @internal
 */
final class CachedTracer implements Tracer
{
    /**
     * This is effectively used as a cache. Note that whilst it would be trivial
     * to extract a CachedTracer implementation, this would make this implementation
     * extremely brittle and unusable without the CachedTracer. Since the coupling
     * is so tight, although we could technically decouple it thanks to interface,
     * I decided against it to make the coupling explicit.
     *
     * @var array<string, Trace|RuntimeException> Traces indexed by their source pathname.
     */
    private array $indexedTraces = [];

    public function __construct(
        private readonly Tracer $decoratedTracer,
    ) {
    }

    public function hasTrace(SplFileInfo $fileInfo): bool
    {
        return $this->tryToTrace($fileInfo) instanceof Trace;
    }

    public function trace(SplFileInfo $fileInfo): Trace
    {
        $trace = $this->tryToTrace($fileInfo);

        if ($trace instanceof Throwable) {
            throw $trace;
        }

        return $trace;
    }

    private function tryToTrace(SplFileInfo $fileInfo): Trace
    {
        $sourcePathname = $fileInfo->getPathname();

        if (!array_key_exists($sourcePathname, $this->indexedTraces)) {
            try {
                $this->indexedTraces[$sourcePathname] = $this->decoratedTracer->trace($fileInfo);
            } catch (RuntimeException $exception) {
                $this->indexedTraces[$sourcePathname] = $exception;
            }
        }

        return $this->indexedTraces[$sourcePathname];
    }
}
