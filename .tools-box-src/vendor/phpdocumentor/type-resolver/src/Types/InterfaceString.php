<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Fqsen;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
/**
@psalm-immutable
*/
final class InterfaceString implements Type
{
    private ?Fqsen $fqsen;
    public function __construct(?Fqsen $fqsen = null)
    {
        $this->fqsen = $fqsen;
    }
    public function getFqsen() : ?Fqsen
    {
        return $this->fqsen;
    }
    public function __toString() : string
    {
        if ($this->fqsen === null) {
            return 'interface-string';
        }
        return 'interface-string<' . (string) $this->fqsen . '>';
    }
}
