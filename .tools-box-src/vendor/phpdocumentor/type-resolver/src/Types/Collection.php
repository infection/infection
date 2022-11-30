<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Fqsen;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
/**
@psalm-immutable
*/
final class Collection extends AbstractList
{
    private ?Fqsen $fqsen;
    public function __construct(?Fqsen $fqsen, Type $valueType, ?Type $keyType = null)
    {
        parent::__construct($valueType, $keyType);
        $this->fqsen = $fqsen;
    }
    public function getFqsen() : ?Fqsen
    {
        return $this->fqsen;
    }
    public function __toString() : string
    {
        $objectType = (string) ($this->fqsen ?? 'object');
        if ($this->keyType === null) {
            return $objectType . '<' . $this->valueType . '>';
        }
        return $objectType . '<' . $this->keyType . ',' . $this->valueType . '>';
    }
}
