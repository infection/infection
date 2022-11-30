<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Compactor;

use _HumbugBoxb47773b41c19\KevinGH\Box\PhpScoper\Scoper;
use Throwable;
final class PhpScoper implements Compactor
{
    public function __construct(private Scoper $scoper)
    {
    }
    public function compact(string $file, string $contents) : string
    {
        try {
            return $this->scoper->scope($file, $contents);
        } catch (Throwable) {
            return $contents;
        }
    }
    public function getScoper() : Scoper
    {
        return $this->scoper;
    }
}
\class_alias('_HumbugBoxb47773b41c19\\KevinGH\\Box\\Compactor\\PhpScoper', 'KevinGH\\Box\\Compactor\\PhpScoper', \false);
