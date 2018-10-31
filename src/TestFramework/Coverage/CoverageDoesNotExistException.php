<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017-2018, Maks Rafalko
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

namespace Infection\TestFramework\Coverage;

use Infection\Console\ConsoleOutput;
use Infection\Console\Exception\InfectionException;

/**
 * @internal
 */
final class CoverageDoesNotExistException extends InfectionException
{
    public static function with(string $coverageIndexFilePath, string $testFrameworkKey, string $tempDir): self
    {
        return new self(
            sprintf(
                'Code Coverage does not exist. File %s is not found. Check %s version Infection was run with and generated config files inside %s. Make sure to either: %s%s',
                $coverageIndexFilePath,
                $testFrameworkKey,
                $tempDir,
                PHP_EOL,
                ConsoleOutput::INFECTION_USAGE_SUGGESTION
            )
        );
    }

    public static function forJunit(string $filePath): self
    {
        return new self(sprintf('Coverage report `junit` is not found in %s', $filePath));
    }

    public static function forFileAtPath(string $fileName, string $path): self
    {
        return new self(sprintf('Source file %s was not found at %s', $fileName, $path));
    }
}
