<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\PharInfo;

use function basename;
use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\remove;
use _HumbugBoxb47773b41c19\ParagonIE\Pharaoh\Pharaoh as ParagoniePharaoh;
final class Pharaoh extends ParagoniePharaoh
{
    private string $fileName;
    private ?PharInfo $pharInfo = null;
    private ?string $path = null;
    public function __construct(string $file, ?string $alias = null)
    {
        parent::__construct($file, $alias);
        $this->fileName = basename($file);
    }
    public function __destruct()
    {
        unset($this->pharInfo);
        parent::__destruct();
        remove($this->tmp);
    }
    public function getFileName() : string
    {
        return $this->fileName;
    }
    public function getPharInfo() : PharInfo
    {
        if (null === $this->pharInfo || $this->path !== $this->phar->getPath()) {
            $this->path = $this->phar->getPath();
            $this->pharInfo = new PharInfo($this->path);
        }
        return $this->pharInfo;
    }
}
