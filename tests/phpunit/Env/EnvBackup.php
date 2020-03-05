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

namespace Infection\Tests\Env;

use function array_key_exists;
use function getenv;
use function Safe\putenv;
use function Safe\sprintf;
use Webmozart\Assert\Assert;

final class EnvBackup
{
    private $environmentVariables;

    /**
     * @param array<string, string> $environmentVariables
     */
    private function __construct(array $environmentVariables)
    {
        $this->environmentVariables = $environmentVariables;
    }

    public static function createSnapshot(): self
    {
        $environmentVariables = getenv();

        Assert::allString($environmentVariables);

        return new self($environmentVariables);
    }

    public function restore(): void
    {
        $snapshot = $this->environmentVariables;

        foreach (getenv() as $name => $value) {
            if (array_key_exists($name, $snapshot)) {
                $snapshotValue = $snapshot[$name];
                unset($snapshot[$name]);

                if ($snapshotValue === $value) {
                    continue;
                }

                putenv(sprintf('%s=%s', $name, $snapshotValue));

                continue;
            }

            putenv($name);
        }

        foreach ($snapshot as $name => $value) {
            putenv(sprintf('%s=%s', $name, $value));
        }
    }
}
