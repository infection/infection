<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type;

/**
@psalm-import-type
@psalm-import-type
@implements
*/
final class RawType implements InputType
{
    public function coerceValue($value, string $label)
    {
        /**
        @psalm-suppress */
        return $value;
    }
    public function getTypeClassNames() : array
    {
        return [self::class];
    }
    public function getPsalmTypeDeclaration() : string
    {
        return 'null|bool|string|list<string>';
    }
    public function getPhpTypeDeclaration() : ?string
    {
        return null;
    }
}
