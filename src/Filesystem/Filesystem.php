<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
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
     * @throws IOException On any directory creation failure
     */
    public function mkdir(string $path, int $mode = 0755)
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

    /**
     * Atomically dumps content into a file.
     *
     * @param string $filename The file to be written to
     * @param string $content  The data to write into the file
     *
     * @throws IOException If the file cannot be written to
     */
    public function dumpFile(string $filename, string $content)
    {
        $dir = dirname($filename);

        if (!is_dir($dir)) {
            $this->mkdir($dir);
        }

        if (!is_writable($dir)) {
            throw new IOException(sprintf('Unable to write to the "%s" directory.', $dir), 0, null, $dir);
        }

        if (false === @file_put_contents($filename, $content)) {
            throw new IOException(sprintf('Failed to write file "%s".', $filename), 0, null, $filename);
        }
    }
}
