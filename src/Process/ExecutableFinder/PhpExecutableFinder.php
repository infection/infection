<?php

declare(strict_types=1);

namespace Infection\Process\ExecutableFinder;

use Infection\Php\ConfigBuilder;
use Symfony\Component\Process\PhpExecutableFinder as BasePhpExecutableFinder;

final class PhpExecutableFinder extends BasePhpExecutableFinder
{
    public function findArguments()
    {
        $arguments = [];

        $tempConfigPath = (string) getenv(ConfigBuilder::ENV_TEMP_PHP_CONFIG_PATH);

        if (!empty($tempConfigPath) && file_exists($tempConfigPath)) {
            $arguments[] = '-c';
            $arguments[] = $tempConfigPath;
        } elseif ('phpdbg' === PHP_SAPI) {
            $arguments[] = '-qrr';
        }

        return $arguments;
    }
}
