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

use function array_fill_keys;
use function array_keys;
use function sprintf;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
abstract class AllowedFunctionsConfig
{
    /**
     * @var string[]
     */
    private readonly array $allowedFunctions;

    /**
     * @param array<string, bool> $settings
     * @param string[] $knownFunctions
     */
    public function __construct(array $settings, array $knownFunctions)
    {
        $filteredSettings = array_fill_keys($knownFunctions, true);

        foreach ($settings as $functionName => $enabled) {
            Assert::boolean(
                $enabled,
                sprintf(
                    'Expected the value for "%s" to be a boolean. Got "%%s" instead',
                    $functionName,
                ),
            );
            Assert::oneOf($functionName, $knownFunctions);

            if (!$enabled) {
                unset($filteredSettings[$functionName]);
            }
        }

        $this->allowedFunctions = array_keys($filteredSettings);
    }

    /**
     * @return string[]
     */
    final public function getAllowedFunctions(): array
    {
        return $this->allowedFunctions;
    }
}
