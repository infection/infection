<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Utils;

use Symfony\Component\Filesystem\Filesystem;

class TmpDirectoryCreator
{
    /**
     * @private
     */
    const BASE_DIR_NAME = 'infection';

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string|null
     */
    private $path;

    public function __construct(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    public function createAndGet(string $tempDir): string
    {
        if ($this->path === null) {
            $path = \sprintf('%s/%s', $tempDir, self::BASE_DIR_NAME);

            $this->fileSystem->mkdir($path, 0777);

            $this->path = $path;
        }

        return $this->path;
    }
}
