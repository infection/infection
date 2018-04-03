<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpUnit\Config\Path;

use Symfony\Component\Filesystem\Filesystem;

final class PathReplacer
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
                '%s/%s',
                $this->phpUnitConfigDir,
                ltrim($domElement->nodeValue, '\/')
            );

            // remove all occurrences of "/./". realpath can't be used because of glob patterns
            $newPath = str_replace('/./', '/', $newPath);

            $domElement->nodeValue = $newPath;
        }
    }
}
