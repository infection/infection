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

namespace Infection\TestFramework\PhpUnit\Mutant;

use Infection\CannotBeInstantiated;
use function Safe\preg_match;

/**
 * @internal
 */
final class PHPUnitMutantOutput
{
    use CannotBeInstantiated;

    public static function testsPass(string $output): bool
    {
        if (preg_match('/failures!/i', $output) === 1) {
            return false;
        }

        if (preg_match('/errors!/i', $output) === 1) {
            return false;
        }

        // OK (XX tests, YY assertions)
        $isOk = preg_match('/OK\s\(/', $output) === 1;

        // "OK, but incomplete, skipped, or risky tests!"
        $isOkWithInfo = preg_match('/OK\s?,/', $output) === 1;

        // "Warnings!" - e.g. when deprecated functions are used, but tests pass
        $isWarning = preg_match('/warnings!/i', $output) === 1;

        // "No tests executed!" - e.g. when --filter option contains too large regular expression
        $isNoTestsExecuted = preg_match('/No tests executed!/i', $output) === 1;

        return $isOk || $isOkWithInfo || $isWarning || $isNoTestsExecuted;
    }

    public static function isSyntaxError(string $output): bool
    {
        return preg_match('/ParseError: syntax error/i', $output) === 1;
    }
}
