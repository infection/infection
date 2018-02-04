<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Php;

final class PhpIniHelper
{
    const ENV_ORIGINALS_PHP_INIS = 'INFECTION_ORIGINAL_INIS';

    /**
     * @return string[] List of php.ini locations
     */
    public static function get(): array
    {
        if ($env = (string) getenv(self::ENV_ORIGINALS_PHP_INIS)) {
            return explode(PATH_SEPARATOR, $env);
        }

        $paths = [(string) php_ini_loaded_file()];

        if ($scanned = php_ini_scanned_files()) {
            $paths = array_merge($paths, array_map('trim', explode(',', $scanned)));
        }

        putenv(self::ENV_ORIGINALS_PHP_INIS . '=' . implode(PATH_SEPARATOR, $paths));

        return $paths;
    }
}
