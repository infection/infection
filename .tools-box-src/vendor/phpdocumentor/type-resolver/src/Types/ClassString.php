<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Fqsen;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoType;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
/**
@psalm-immutable
*/
final class ClassString extends String_ implements PseudoType
{
    private ?Fqsen $fqsen;
    public function __construct(?Fqsen $fqsen = null)
    {
        $this->fqsen = $fqsen;
    }
    public function underlyingType() : Type
    {
        return new String_();
    }
    public function getFqsen() : ?Fqsen
    {
        return $this->fqsen;
    }
    public function __toString() : string
    {
        if ($this->fqsen === null) {
            return 'class-string';
        }
        return 'class-string<' . (string) $this->fqsen . '>';
    }
}
