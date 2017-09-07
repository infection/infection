<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Filesystem;

use Infection\Filesystem\Exception\IOException;

class Filesystem
{
    /**
     * Create a directory recursively.
     *
     * @param string $path The directory path
     * @param int $mode The directory mode
     *
     * @throws \RuntimeException On any directory creation failure
     */
    public function mkdir($path, int $mode = 0755)
    {
        if (\is_dir($path)) {
            return;
        }

        if (true !== @\mkdir($path, $mode, true)) {
            $error = \error_get_last();

            if ($error) {
                throw new IOException(\sprintf('Failed to create "%s": %s', $path, $error['message']), 0, null, $path);
            }

            throw new IOException(\sprintf('Failed to create "%s"', $path), 0, null, $path);
        }
    }
}