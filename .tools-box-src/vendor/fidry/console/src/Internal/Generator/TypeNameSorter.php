<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Generator;

use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\InputType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\ListType;
use _HumbugBoxb47773b41c19\Fidry\Console\Internal\Type\NonEmptyListType;
use function array_slice;
use function in_array;
final class TypeNameSorter
{
    private const INVERSE_TYPE_CLASS_NAMES = [ListType::class, NonEmptyListType::class];
    private function __construct()
    {
    }
    public static function sortClassNames(array $typeClassNames) : array
    {
        $sortedTypes = [];
        self::traverseAndCollectTypes($typeClassNames, $sortedTypes);
        return $sortedTypes;
    }
    private static function traverseAndCollectTypes(array $unsortedTypes, array &$sortedTypes) : void
    {
        foreach ($unsortedTypes as $index => $unsortedType) {
            if (!in_array($unsortedType, self::INVERSE_TYPE_CLASS_NAMES, \true)) {
                $sortedTypes[] = $unsortedType;
                continue;
            }
            $listInnerTypes = array_slice($unsortedTypes, $index + 1);
            self::traverseAndCollectTypes($listInnerTypes, $sortedTypes);
            $sortedTypes[] = $unsortedType;
            break;
        }
    }
}
