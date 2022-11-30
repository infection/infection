<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Compactor;

abstract class BaseCompactor implements Compactor
{
    public function compact(string $file, string $contents) : string
    {
        if ($this->supports($file)) {
            return $this->compactContent($contents);
        }
        return $contents;
    }
    protected abstract function compactContent(string $contents) : string;
    protected abstract function supports(string $file) : bool;
}
