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

namespace Infection\Benchmark\ParseGitDiff;

use function fclose;
use function fopen;
use function fwrite;
use InvalidArgumentException;
use Random\Engine\Secure;
use Random\Randomizer;
use function unlink;

require_once __DIR__ . '/generator.php';

$target = __DIR__ . '/diff';
// @phpstan-ignore theCodingMachineSafe.function
@unlink($target);

// @phpstan-ignore theCodingMachineSafe.function
$targetHandle = fopen($target, 'a');

if ($targetHandle === false) {
    throw new InvalidArgumentException('Failed to open file: ' . $target);
}

try {
    Generator::generate(
        config: Configuration::create(),
        randomizer: new Randomizer(new Secure()),
        io: new IO(
            // @phpstan-ignore theCodingMachineSafe.function
            write: static fn (string $content) => fwrite($targetHandle, $content),
            writeError: IO::writeToStdErr(...),
        ),
    );
} finally {
    // @phpstan-ignore theCodingMachineSafe.function
    fclose($targetHandle);
}
