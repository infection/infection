<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Utils;

use Infection\Filesystem\Filesystem;

class TempDirectoryCreator
{
    const BASE_DIR_NAME = 'infection';

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var string
     */
    private $tempDirectoryPath;

    /**
     * @param Filesystem $fs
     */
    public function __construct(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    public function createAndGet(string $tempDir): string
    {
        if ($this->tempDirectoryPath === null) {
            $path = sprintf('%s/%s', $tempDir, self::BASE_DIR_NAME);

            $this->fs->mkdir($path, 0777);

            $this->tempDirectoryPath = $path;
        }

        return $this->tempDirectoryPath;
    }
}
