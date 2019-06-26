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

use SebastianBergmann\Diff\Differ as BaseDiffer;

/**
 * @internal
 */
class Differ
{
    public const DIFF_MAX_LINES = 12;

    /**
     * @var BaseDiffer
     */
    private $differ;

    public function __construct(BaseDiffer $differ)
    {
        $this->differ = $differ;
    }

    /**
     * Returns the diff between two arrays or strings as string.
     *
     * Overridden to show just DIFF_MAX_LINES lines of the diff
     *
     * @param array|string $from
     * @param array|string $to
     */
    public function diff($from, $to): string
    {
        $diff = $this->differ->diff($from, $to);

        if (preg_match(
            '/(?:[^\n]*(\n)){' . self::DIFF_MAX_LINES . '}/',
            $diff,
            $matches,
            PREG_OFFSET_CAPTURE
        )) {
            $diff = substr($diff, 0, $matches[1][1]);
        }

        return $diff;
    }
}
