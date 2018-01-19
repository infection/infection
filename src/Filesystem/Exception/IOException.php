<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Filesystem\Exception;

class IOException extends \RuntimeException
{
    public static function directoryNotWritable(string $dir): self
    {
        return new self(
            sprintf(
                'Unable to write to the "%s" directory.',
                $dir
            )
        );
    }

    public static function unableToCreate(string $path, string $message = null): self
    {
        $toThrow = sprintf('Failed to create "%s"', $path);
        if ($message !== null) {
            $toThrow .= sprintf(': %s', $message);
        }

        return new self($toThrow);
    }

    public static function unableToWriteToFile(string $filename): self
    {
        return new self(
            sprintf(
                'Failed to write file "%s".',
                $filename
            )
        );
    }
}
