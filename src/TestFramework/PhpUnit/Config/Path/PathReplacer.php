<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config\Path;

use Symfony\Component\Filesystem\Filesystem;

class PathReplacer
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string|null
     */
    private $phpUnitConfigDir;

    public function __construct(Filesystem $filesystem, string $phpUnitConfigDir = null)
    {
        $this->filesystem = $filesystem;
        $this->phpUnitConfigDir = $phpUnitConfigDir;
    }

    /**
     * @param \DOMNode|\DOMElement $domElement
     */
    public function replaceInNode(\DOMNode $domElement)
    {
        if (!$this->filesystem->isAbsolutePath($domElement->nodeValue)) {
            $newPath = sprintf(
                '%s%s%s',
                $this->phpUnitConfigDir,
                DIRECTORY_SEPARATOR,
                ltrim($domElement->nodeValue, '\/')
            );

            // remove all occurrences of "/./". realpath can't be used because of glob patterns
            $newPath = str_replace(
                sprintf('%s.%s', DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR),
                DIRECTORY_SEPARATOR,
                $newPath
            );

            $domElement->nodeValue = $newPath;
        }
    }
}
