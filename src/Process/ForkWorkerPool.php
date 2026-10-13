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

namespace Infection\Process;

use function register_shutdown_function;

/**
 * Owns the fork workers of a run: one worker per thread, each a PHP process that stays alive while
 * its thread runs mutants. A worker outlives every process of its thread, so no process can own it;
 * the pool does.
 *
 * @internal
 */
final class ForkWorkerPool
{
    /**
     * @var array<string, ForkWorker>
     */
    private array $workers = [];

    public function __construct()
    {
        // The workers are the only processes of a run that outlive the process that asked for them:
        // a thread keeps its worker from its first mutant to the end of the run. The registered
        // handler is what stops them, because the shutdown of this process is the end of the run -
        // Infection runs one Engine per process. A pool that ends its run early calls killAll().
        register_shutdown_function($this->killAll(...));
    }

    /**
     * Returns the worker of a thread, and starts one the first time the thread asks for it.
     *
     * @param string $threadToken the TEST_TOKEN of the thread, as an environment value
     */
    public function workerFor(string $threadToken): ForkWorker
    {
        return $this->workers[$threadToken] ??= ForkWorker::start();
    }

    /**
     * Forgets the worker of a thread. A killed worker is not reusable, so the next run of the thread
     * starts a new one.
     */
    public function forget(string $threadToken): void
    {
        unset($this->workers[$threadToken]);
    }

    public function killAll(): void
    {
        foreach ($this->workers as $worker) {
            $worker->kill();
        }

        $this->workers = [];
    }
}
