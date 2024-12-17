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

namespace Infection\TestFramework;

use Composer\Autoload\ClassLoader;
use Infection\FileSystem\Finder\ComposerExecutableFinder;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final readonly class AdapterInstaller
{
    public const OFFICIAL_ADAPTERS_MAP = [
        TestFrameworkTypes::CODECEPTION => 'infection/codeception-adapter',
        TestFrameworkTypes::PHPSPEC => 'infection/phpspec-adapter',
    ];

    // 2 minutes
    private const TIMEOUT = 120.0;

    public function __construct(private ComposerExecutableFinder $composerExecutableFinder)
    {
    }

    public function install(string $adapterName): void
    {
        Assert::keyExists(self::OFFICIAL_ADAPTERS_MAP, $adapterName);

        $process = new Process([
            $this->composerExecutableFinder->find(),
            'require',
            '--dev',
            self::OFFICIAL_ADAPTERS_MAP[$adapterName],
        ]);

        $process->setTimeout(self::TIMEOUT);

        $process->run();

        $loader = new ClassLoader();

        /** @var array<string, string[]> $map */
        $map = require 'vendor/composer/autoload_psr4.php';

        foreach ($map as $namespace => $paths) {
            $loader->setPsr4($namespace, $paths);
        }

        $loader->register(false);
    }
}
