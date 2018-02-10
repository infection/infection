<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Finder\Exception;

class LocatorException extends \RuntimeException
{
    public static function fileOrDirectorieDoesNotExist(string $name)
    {
        return new self(sprintf('The file/directory "%s" does not exist.', $name));
    }

    public function filesOrDirectoriesDoNotExist(string $name, array $paths)
    {
        return new self(
            sprintf('The file/folder "%s" does not exist (in: %s).', $name, implode(', ', $paths))
        );
    }

    public static function filesNotFound()
    {
        return new self('Files are not found');
    }
}
