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

namespace Infection\Source\Exception;

use Infection\Configuration\SourceFilter\PlainFilter;
use RuntimeException;
use function sprintf;
use Throwable;
use function trim;

/**
 * @internal
 */
final class NoSourceFound extends RuntimeException
{
    public function __construct(
        public readonly bool $isSourceFiltered,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function noFilesForGitDiff(string $diffFilter, string $base): self
    {
        return new self(
            isSourceFiltered: true,
            message: sprintf(
                'Could not find any modified files in the configured sources for the git filter "%s" and the base "%s".',
                $diffFilter,
                $base,
            ),
        );
    }

    public static function noChangedLinesForGitDiff(
        string $diffFilter,
        string $base,
        string $diff,
    ): self {
        $isDiffBlank = trim($diff) === '';

        return new self(
            isSourceFiltered: true,
            message: sprintf(
                $isDiffBlank
                ? 'Could not find any modified lines for the git filter "%s" and the base "%s". The diff got was blank.'
                : <<<'EOF'
                    Could not find any modified lines for the git filter "%s" and the base "%s". The diff got was:

                    """
                    %s
                    """
                    EOF,
                $diffFilter,
                $base,
                $diff,
            ),
        );
    }

    public static function noSourceFileFound(?PlainFilter $filter): self
    {
        return new self(
            isSourceFiltered: $filter !== null,
            message: $filter !== null
                ? sprintf(
                    'No source file found for the filter applied to the configured sources. The filter used was: "%s".',
                    $filter->toString(),
                )
                : 'No source file found for the configured sources.',
        );
    }

    public static function noExecutableSourceCodeForDiff(): self
    {
        return new self(
            isSourceFiltered: true,
            message: 'No source code from the diff was executed by the test framework.',
        );
    }

    public static function noExecutableSourceCode(): self
    {
        return new self(
            isSourceFiltered: false,
            message: 'No source code was executed by the test framework.',
        );
    }
}
