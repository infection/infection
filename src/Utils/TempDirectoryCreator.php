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

    public function createAndGet(string $dirName = null): string
    {
        if ($this->tempDirectory === null) {
            $path = sprintf(
                '%s/%s',
                sys_get_temp_dir(),
                $dirName ?: 'infection'
            );

            if (!@mkdir($path, 0777, true) && !is_dir($path)) {
                throw new \RuntimeException('Can not create temp dir');
            }

            $this->tempDirectory = $path;
        }

        return $this->tempDirectory;
    }
}
