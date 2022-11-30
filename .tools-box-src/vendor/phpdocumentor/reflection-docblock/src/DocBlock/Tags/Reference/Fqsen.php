<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags\Reference;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Fqsen as RealFqsen;
final class Fqsen implements Reference
{
    private $fqsen;
    public function __construct(RealFqsen $fqsen)
    {
        $this->fqsen = $fqsen;
    }
    public function __toString() : string
    {
        return (string) $this->fqsen;
    }
}
