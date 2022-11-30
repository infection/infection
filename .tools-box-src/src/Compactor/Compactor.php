<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Compactor;

interface Compactor
{
    public function compact(string $file, string $contents) : string;
}
\class_alias('_HumbugBoxb47773b41c19\\KevinGH\\Box\\Compactor\\Compactor', 'KevinGH\\Box\\Compactor\\Compactor', \false);
