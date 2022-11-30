<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Generator;

use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\BooleanType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\FloatType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\InputType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\ListType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NaturalType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyListType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyStringType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NullableType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NullOrNonEmptyStringType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\PositiveIntegerType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\RawType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\StringType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\UntrimmedStringType;
use function array_merge;
final class TypeMap
{
    private function __construct()
    {
    }
    public static function provideTypes() : array
    {
        $baseTypes = [BooleanType::class, NaturalType::class, PositiveIntegerType::class, FloatType::class, StringType::class, NonEmptyStringType::class, UntrimmedStringType::class];
        $types = [self::createTypes(RawType::class, \false, \false)];
        foreach ($baseTypes as $baseType) {
            $types[] = self::createTypes($baseType, \true, \true);
        }
        $types[] = self::createTypes(NullOrNonEmptyStringType::class, \false, \true);
        return array_merge(...$types);
    }
    private static function createTypes(string $typeClassName, bool $nullable, bool $list) : array
    {
        $types = [$type = new $typeClassName()];
        if ($nullable) {
            $types[] = new NullableType($type);
        }
        if ($list) {
            $types[] = new ListType($type);
            $types[] = new NonEmptyListType($type);
        }
        return $types;
    }
}
