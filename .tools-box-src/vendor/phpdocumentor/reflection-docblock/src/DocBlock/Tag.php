<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Formatter;
interface Tag
{
    public function getName() : string;
    /**
    @phpstan-return
    */
    public static function create(string $body);
    public function render(?Formatter $formatter = null) : string;
    public function __toString() : string;
}
