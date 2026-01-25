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

namespace Infection\Tests\Logger\MutationAnalysis\TeamCity;

use function count;
use function explode;
use function implode;
use Infection\CannotBeInstantiated;
use function trim;

/**
 * This service is a utility to make the TeamCity logs more readable by indenting
 * them based on the opening/closing blocks.
 *
 * Note that this is purely for testing purposes for better readability: teamcity
 * logs do not need to be indented.
 */
final class RemoveInternalBlankLines
{
    use CannotBeInstantiated;

    public static function remove(string $lines): string
    {
        $lineArray = explode("\n", $lines);
        $lineCount = count($lineArray);

        $firstNonBlank = null;
        $lastNonBlank = null;

        foreach ($lineArray as $i => $line) {
            if (trim($line) !== '') {
                $firstNonBlank ??= $i;
                $lastNonBlank = $i;
            }
        }

        if ($firstNonBlank === null) {
            return $lines;
        }

        $result = [];

        for ($i = 0; $i < $firstNonBlank; ++$i) {
            $result[] = $lineArray[$i];
        }

        for ($i = $firstNonBlank; $i <= $lastNonBlank; ++$i) {
            if (trim($lineArray[$i]) !== '') {
                $result[] = $lineArray[$i];
            }
        }

        for ($i = $lastNonBlank + 1; $i < $lineCount; ++$i) {
            $result[] = $lineArray[$i];
        }

        return implode("\n", $result);
    }
}
