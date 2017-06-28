<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Utils;

class TempDirectoryCreator
{
    /**
     * @var string
     */
    private $tempDirectory;

    public function createAndGet($dirName = null) : string
    {
        if ($this->tempDirectory === null) {
            $root = sys_get_temp_dir();
            $path = $root . sprintf('/%s', $dirName ?: 'infection');

            if (! @mkdir($path, 0777, true) && !is_dir($path)) {
                throw new \RuntimeException('Can not create temp dir');
            }

            $this->tempDirectory = $path;
        }

        return $this->tempDirectory;
    }
}