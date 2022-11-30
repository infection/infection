<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config\Path;

use DOMElement;
use DOMNode;
use function ltrim;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function str_replace;
use _HumbugBox9658796bb9f0\Symfony\Component\Filesystem\Filesystem;
use function trim;
final class PathReplacer
{
    public function __construct(private Filesystem $filesystem, private ?string $phpUnitConfigDir = null)
    {
    }
    public function replaceInNode(DOMElement|DOMNode $domElement) : void
    {
        $path = trim($domElement->nodeValue);
        if (!$this->filesystem->isAbsolutePath($path)) {
            $newPath = sprintf('%s/%s', $this->phpUnitConfigDir, ltrim($path, '\\/'));
            $newPath = str_replace('/./', '/', $newPath);
            $domElement->nodeValue = $newPath;
        }
    }
}
