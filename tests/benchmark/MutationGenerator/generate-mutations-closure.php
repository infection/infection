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

namespace Infection\Benchmark\MutationGenerator;

use function array_map;
use Infection\Container;
use Infection\TestFramework\Coverage\Trace;
use function iterator_to_array;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

require_once __DIR__ . '/../../../vendor/autoload.php';

$container = Container::create()->withDynamicParameters(
    null,
    '',
    false,
    'default',
    false,
    false,
    'dot',
    false,
    '',
    '',
    false,
    false,
    .0,
    .0,
    'phpunit',
    '',
    ''
);

$files = Finder::create()
    ->files()
    ->in(__DIR__ . '/sources')
    ->name('*.php')
;

// Since those files are not autoloaded, we need to manually autoload them
require_once __DIR__ . '/sources/autoload.php';

$traces = array_map(
    static function (SplFileInfo $fileInfo): Trace {
        require_once $fileInfo->getRealPath();

        return new PartialTrace($fileInfo);
    },
    iterator_to_array($files, false)
);

$mutators = $container->getMutatorFactory()->create(
    $container->getMutatorResolver()->resolve(['@default' => true])
);

$fileMutationGenerator = $container->getFileMutationGenerator();

return static function (int $maxCount) use ($fileMutationGenerator, $traces, $mutators): void {
    if ($maxCount < 0) {
        $maxCount = null;
    }

    $count = 0;

    foreach ($traces as $trace) {
        $mutations = $fileMutationGenerator->generate(
            $trace,
            false,
            $mutators,
            []
        );

        foreach ($mutations as $_) {
            ++$count;

            if ($maxCount !== null && $count === $maxCount) {
                return;
            }
        }
    }
};
