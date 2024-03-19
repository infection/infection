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

namespace Infection\Configuration\Entry;

use InvalidArgumentException;
use function preg_quote;
use Safe\Exceptions\PcreException;
use function Safe\preg_match;
use function sprintf;

/**
 * @internal
 */
final class StrykerConfig
{
    private string $branchMatch;

    /**
     * Stryker has 2 ways for integration (https://stryker-mutator.io/docs/General/dashboard):
     *  - badge only
     *  - full report
     *
     * @throws InvalidArgumentException when the provided $branch looks like a regular expression, but is not a valid one
     */
    private function __construct(string $branch, private readonly bool $isForFullReport)
    {
        if (preg_match('#^/.+/$#', $branch) === 0) {
            $this->branchMatch = '/^' . preg_quote($branch, '/') . '$/';

            return;
        }

        try {
            // Yes, the `@` is intentional. For some reason, `thecodingmachine/safe` does not suppress the warnings here
            @preg_match($branch, '');
        } catch (PcreException $invalidRegex) {
            throw new InvalidArgumentException(
                sprintf('Provided branchMatchRegex "%s" is not a valid regex', $branch),
                0,
                $invalidRegex,
            );
        }

        $this->branchMatch = $branch;
    }

    public static function forBadge(string $branch): self
    {
        return new self($branch, false);
    }

    public static function forFullReport(string $branch): self
    {
        return new self($branch, true);
    }

    public function isForFullReport(): bool
    {
        return $this->isForFullReport;
    }

    public function applicableForBranch(string $branchName): bool
    {
        return preg_match($this->branchMatch, $branchName) === 1;
    }
}
