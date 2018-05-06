<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Finder\Exception;

/**
 * @internal
 */
final class LocatorException extends \RuntimeException
{
    public static function fileOrDirectoryDoesNotExist(string $name): self
    {
        return new self(sprintf('The file/directory "%s" does not exist.', $name));
    }

    public static function filesOrDirectoriesDoNotExist(string $name, array $paths): self
    {
        return new self(
            sprintf('The file/folder "%s" does not exist (in: %s).', $name, implode(', ', $paths))
        );
    }

    public static function multipleFilesDoNotExist(string $path, array $files): self
    {
        return new self(
            sprintf(
                'The path %s does not contain any of the requested files: %s',
                $path,
                implode(', ', $files)
            )
        );
    }

    public static function filesNotFound(): self
    {
        return new self('Files are not found');
    }
}
