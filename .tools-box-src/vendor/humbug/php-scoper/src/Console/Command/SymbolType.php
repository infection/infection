<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Console\Command;

use function array_column;
enum SymbolType : string
{
    case CLASS_TYPE = 'class';
    case FUNCTION_TYPE = 'function';
    case CONSTANT_TYPE = 'constant';
    case ANY_TYPE = 'any';
    public const ALL = [self::CLASS_TYPE, self::FUNCTION_TYPE, self::CONSTANT_TYPE, self::ANY_TYPE];
    public static function getAllSpecificTypes() : array
    {
        return [self::CLASS_TYPE, self::FUNCTION_TYPE, self::CONSTANT_TYPE];
    }
    public static function values() : array
    {
        return array_column(self::cases(), 'value');
    }
}
