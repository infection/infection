<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoTypes;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\PseudoType;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Types\Boolean;
use function class_alias;
/**
@psalm-immutable
*/
final class True_ extends Boolean implements PseudoType
{
    public function underlyingType() : Type
    {
        return new Boolean();
    }
    public function __toString() : string
    {
        return 'true';
    }
}
class_alias(True_::class, '_HumbugBoxb47773b41c19\\phpDocumentor\\Reflection\\Types\\True_', \false);
