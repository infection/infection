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

namespace Infection\Benchmark;

use BlackfireProbe;
use Closure;
use Composer\Autoload\ClassLoader;
use function extension_loaded;
use function Safe\sprintf;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class BlackfireInstrumentor
{
    private function __construct()
    {
    }

    /**
     * @param Closure(): void $main
     */
    public static function profile(Closure $main, SymfonyStyle $io): void
    {
        self::check($io);

        $probe = BlackfireProbe::getMainInstance();

        $probe->enable();

        try {
            $main();

            $probe->disable();
        } catch (Throwable $throwable) {
            $probe->discard();

            $io->warning(sprintf(
                'An error occurred. The profile has been discarded please check the error first: "%s"',
                $throwable->getMessage()
            ));

            throw $throwable;
        }
    }

    private static function check(SymfonyStyle $io): void
    {
        if (!extension_loaded('blackfire')) {
            $io->error(sprintf(
                'Could not find the blackfire extension. Make sure blackfire is properly '
                . 'installed. See "%s" or "%s"',
                'https://blackfire.io/docs/up-and-running/installation',
                'https://support.blackfire.io/en/collections/145104-troubleshooting'
            ));

            exit(1);
        }

        if (extension_loaded('pcov')) {
            $io->error(sprintf(
                'The extension pcov is enabled and will result in an unusable Blackfire '
                . 'profile. Make sure it is disable and for more informations you can check '
                . '<info>%s</info>',
                'https://support.blackfire.io/en/articles/3669196-known-incompatibilities-with-the-php-probe'
            ));

            exit(1);
        }

        if (extension_loaded('xdebug')) {
            $io->warning('Xdebug has been detected. Be aware that this may severely affect the results');
        }

        /** @var ClassLoader $composerAutoloader */
        $composerAutoloader = require __DIR__ . '/../../vendor/autoload.php';

        Assert::isInstanceOf($composerAutoloader, ClassLoader::class);

        if (!$composerAutoloader->isClassMapAuthoritative()) {
            $io->warning(sprintf(
                'The composer autoloader is not set in classmap authoritative mode which can'
                . ' result in an unnecessary overhead. Consider running the command %s',
                'composer dump-autoload --classmap-authoritative'
            ));
        }
    }
}
