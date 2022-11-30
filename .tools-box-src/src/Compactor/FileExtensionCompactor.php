<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Compactor;

use function in_array;
use function pathinfo;
use const PATHINFO_EXTENSION;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
abstract class FileExtensionCompactor extends BaseCompactor
{
    private array $extensions;
    public function __construct(array $extensions)
    {
        Assert::allString($extensions);
        $this->extensions = $extensions;
    }
    protected function supports(string $file) : bool
    {
        return in_array(pathinfo($file, PATHINFO_EXTENSION), $this->extensions, \true);
    }
}
