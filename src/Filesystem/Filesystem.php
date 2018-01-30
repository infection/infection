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
                throw IOException::unableToCreate($path, $error['message']);
            }

            throw IOException::unableToCreate($path);
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
            throw IOException::directoryNotWritable($dir);
        }

        if (false === @file_put_contents($filename, $content)) {
            throw IOException::unableToWriteToFile($filename);
        }
    }

    /**
     * Removes files or directories.
     *
     * @param string|iterable $files A filename, an array of files, or a \Traversable instance to remove
     *
     * @throws IOException When removal fails
     */
    public function remove($files)
    {
        if ($files instanceof \Traversable) {
            $files = iterator_to_array($files, false);
        } elseif (!is_array($files)) {
            $files = [$files];
        }

        $files = array_reverse($files);

        foreach ($files as $file) {
            if (is_link($file)) {
                if (!@(unlink($file) || '\\' !== DIRECTORY_SEPARATOR || rmdir($file)) && file_exists($file)) {
                    $error = error_get_last();

                    throw IOException::unableToRemoveSymlink($file, $error['message']);
                }
            } elseif (is_dir($file)) {
                $this->remove(new \FilesystemIterator($file, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS));

                if (!@rmdir($file) && file_exists($file)) {
                    $error = error_get_last();

                    throw IOException::unableToRemoveDirectory($file, $error['message']);
                }
            } elseif (!@unlink($file) && file_exists($file)) {
                $error = error_get_last();

                throw IOException::unableToRemoveFile($file, $error['message']);
            }
        }
    }
}
