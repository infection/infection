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

namespace Infection\Mutator;

use function array_flip;
use function array_key_exists;
use const FNM_NOESCAPE;
use function fnmatch;

/**
 * @internal
 * @final
 */
class IgnoreConfig
{
    /** @var array<string, int> */
    private array $hashtable = [];

    /**
     * @param string[] $patterns
     */
    public function __construct(private readonly array $patterns)
    {
        if ($patterns !== []) {
            $this->hashtable = array_flip($patterns);
        }
    }

    public function isIgnored(string $class, string $method, ?int $lineNumber): bool
    {
        if ($this->patterns === []) {
            return false;
        }

        if (array_key_exists($class, $this->hashtable)) {
            return true;
        }

        $classMethod = $class . '::' . $method;

        if (array_key_exists($classMethod, $this->hashtable)) {
            return true;
        }

        foreach ($this->patterns as $ignorePattern) {
            if (fnmatch($ignorePattern, $class, FNM_NOESCAPE)
                || fnmatch($ignorePattern, $classMethod, FNM_NOESCAPE)
                || ($lineNumber !== null && fnmatch($ignorePattern, $classMethod . '::' . $lineNumber, FNM_NOESCAPE))
            ) {
                return true;
            }
        }

        return false;
    }
}
