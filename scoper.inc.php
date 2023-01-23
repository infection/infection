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

use Isolated\Symfony\Component\Finder\Finder;

// see https://github.com/humbug/php-scoper/blob/0ed64857e86ae9ea4f5889c34e153f2cdbe968b5/docs/further-reading.md#polyfills
$polyfillsBootstraps = \array_map(
    static fn (SplFileInfo $fileInfo) => $fileInfo->getPathname(),
    \iterator_to_array(
        Finder::create()
            ->files()
            ->in(__DIR__ . '/vendor/symfony/polyfill-*')
            ->name('bootstrap*.php'),
        false,
    ),
);

$polyfillsStubs = \array_map(
    static fn (SplFileInfo $fileInfo) => $fileInfo->getPathname(),
    \iterator_to_array(
        Finder::create()
            ->files()
            ->in(__DIR__ . '/vendor/symfony/polyfill-*/Resources/stubs')
            ->name('*.php'),
        false,
    ),
);

return [
    'whitelist' => [
        // PHP 8.0
        'T_NAME_QUALIFIED',
        'T_NAME_FULLY_QUALIFIED',
        'T_NAME_RELATIVE',
        'T_MATCH',
        'T_NULLSAFE_OBJECT_OPERATOR',
        'T_ATTRIBUTE',
        // PHP 8.1
        'T_AMPERSAND_NOT_FOLLOWED_BY_VAR_OR_VARARG',
        'T_AMPERSAND_FOLLOWED_BY_VAR_OR_VARARG',
        'T_ENUM',
        'T_READONLY',
    ],
    'prefix' => 'Infected',
    'exclude-classes' => [\Composer\InstalledVersions::class],
    'exclude-namespaces' => [
        'Symfony\Polyfill',
    ],
    'exclude-constants' => [
        // Symfony global constants
        '/^SYMFONY\_[\p{L}_]+$/',
    ],
    'exclude-files' => [
        'vendor/composer/InstalledVersions.php',
        ...$polyfillsBootstraps,
        ...$polyfillsStubs,
    ],
];
