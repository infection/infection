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

namespace Infection\FileSystem\Finder\Iterator;

use Infection\Framework\OperatingSystem;
use function preg_quote;
use Safe\Exceptions\PcreException;
use function Safe\preg_match;
use function sprintf;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Iterator\MultiplePcreFilterIterator;

/**
 * @internal
 *
 * @template-covariant TKey
 * @template-covariant TValue
 *
 * @extends MultiplePcreFilterIterator<TKey, TValue>
 */
final class RealPathFilterIterator extends MultiplePcreFilterIterator
{
    public function accept(): bool
    {
        $filename = $this->current()->getRealPath();

        if (OperatingSystem::isWindows()) {
            $filename = Path::normalize((string) $filename);
        }

        return $this->isAccepted($filename);
    }

    /**
     * Converts strings to regexp.
     *
     * PCRE patterns are left unchanged.
     *
     * Default conversion:
     *     'lorem/ipsum/dolor' ==>  'lorem\/ipsum\/dolor/'
     *
     * Literal paths use / separators to match the normalised file paths.
     *
     * @param string $str Pattern: regexp or dirname
     *
     * @return string regexp corresponding to a given string or regexp
     */
    protected function toRegex(string $str): string
    {
        return $this->isValidRegex($str)
            ? $str
            : sprintf(
                '/%s/',
                preg_quote(
                    Path::normalize($str),
                    '/',
                ),
            );
    }

    private function isValidRegex(string $string): bool
    {
        // ::isRegex() only checks whether the string looks like a regex, so
        // "/project/src/Differ/" passes because it starts and ends with "/".
        // We therefore also evaluate the pattern to check its validity. This
        //
        // Note that it remains ambiguous for strings such as "/tmp/", which
        // can be either a literal path or a valid regex.
        if (!$this->isRegex($string)) {
            return false;
        }

        try {
            // Safe throws on invalid patterns but does not suppress PHP's compilation warning.
            @preg_match($string, '');

            return true;
        } catch (PcreException) {
            return false;
        }
    }
}
