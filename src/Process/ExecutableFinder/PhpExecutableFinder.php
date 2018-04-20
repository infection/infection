<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process\ExecutableFinder;

use Symfony\Component\Process\PhpExecutableFinder as BasePhpExecutableFinder;

final class PhpExecutableFinder extends BasePhpExecutableFinder
{
    public function findArguments()
    {
        $arguments = [];

        if ('phpdbg' == PHP_SAPI) {
            $arguments[] = '-qrr';
        }

        return $arguments;
    }
}
