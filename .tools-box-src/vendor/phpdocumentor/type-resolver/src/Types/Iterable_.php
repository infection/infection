<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types;

/**
@psalm-immutable
*/
final class Iterable_ extends AbstractList
{
    public function __toString() : string
    {
        if ($this->keyType) {
            return 'iterable<' . $this->keyType . ',' . $this->valueType . '>';
        }
        if ($this->valueType instanceof Mixed_) {
            return 'iterable';
        }
        return 'iterable<' . $this->valueType . '>';
    }
}
