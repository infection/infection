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

namespace Infection\Differ;

use function Safe\preg_match;
use function str_contains;

/**
 * @internal
 */
final class DiffSourceCodeMatcher
{
    private const POSSIBLE_DELIMITERS = [
        '#', '%', ':', ';', '=', '?', '@', '^', '~',
    ];

    public function matches(string $diff, string $sourceCodeRegex): bool
    {
        // https://www.php.net/manual/en/regexp.reference.delimiters.php

        $delimiter = $this->findDelimiter($sourceCodeRegex);
        // There's no need to escape delimiters since we're assuming there's none.

        return preg_match("{$delimiter}^-\s*{$sourceCodeRegex}\${$delimiter}mu", $diff) === 1;
    }

    private function findDelimiter(string $sourceCodeRegex): string
    {
        foreach (self::POSSIBLE_DELIMITERS as $possibleDelimiter) {
            if (!str_contains($sourceCodeRegex, $possibleDelimiter)) {
                return $possibleDelimiter;
            }
        }

        // Let it fail naturally, for now. Later this might be a good place to throw an exception.
        return '/';
    }
}
