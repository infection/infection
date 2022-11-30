<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

/**
@psalm-import-type
@psalm-import-type
@template
*/
interface InputType
{
    public function coerceValue($value, string $label);
    public function getTypeClassNames() : array;
    public function getPsalmTypeDeclaration() : string;
    public function getPhpTypeDeclaration() : ?string;
}
