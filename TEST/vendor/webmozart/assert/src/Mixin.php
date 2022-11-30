<?php

namespace _HumbugBox9658796bb9f0\Webmozart\Assert;

use ArrayAccess;
use Closure;
use Countable;
use InvalidArgumentException;
use Throwable;
interface Mixin
{
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrString($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allString($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrStringNotEmpty($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allStringNotEmpty($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrInteger($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allInteger($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrIntegerish($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allIntegerish($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrFloat($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allFloat($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrNumeric($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allNumeric($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrNatural($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allNatural($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrBoolean($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allBoolean($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrScalar($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allScalar($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrObject($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allObject($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrResource($value, $type = null, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allResource($value, $type = null, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrIsCallable($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allIsCallable($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrIsArray($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allIsArray($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrIsTraversable($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allIsTraversable($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrIsArrayAccessible($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allIsArrayAccessible($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrIsCountable($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allIsCountable($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrIsIterable($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allIsIterable($value, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function nullOrIsInstanceOf($value, $class, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function allIsInstanceOf($value, $class, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    */
    public static function nullOrNotInstanceOf($value, $class, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    */
    public static function allNotInstanceOf($value, $class, $message = '');
    /**
    @psalm-pure
    @psalm-param
    */
    public static function nullOrIsInstanceOfAny($value, $classes, $message = '');
    /**
    @psalm-pure
    @psalm-param
    */
    public static function allIsInstanceOfAny($value, $classes, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function nullOrIsAOf($value, $class, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function allIsAOf($value, $class, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    */
    public static function nullOrIsNotA($value, $class, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    */
    public static function allIsNotA($value, $class, $message = '');
    /**
    @psalm-pure
    @psalm-param
    */
    public static function nullOrIsAnyOf($value, $classes, $message = '');
    /**
    @psalm-pure
    @psalm-param
    */
    public static function allIsAnyOf($value, $classes, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrIsEmpty($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allIsEmpty($value, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrNotEmpty($value, $message = '');
    /**
    @psalm-pure
    */
    public static function allNotEmpty($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allNull($value, $message = '');
    /**
    @psalm-pure
    */
    public static function allNotNull($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrTrue($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allTrue($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrFalse($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allFalse($value, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrNotFalse($value, $message = '');
    /**
    @psalm-pure
    */
    public static function allNotFalse($value, $message = '');
    public static function nullOrIp($value, $message = '');
    public static function allIp($value, $message = '');
    public static function nullOrIpv4($value, $message = '');
    public static function allIpv4($value, $message = '');
    public static function nullOrIpv6($value, $message = '');
    public static function allIpv6($value, $message = '');
    public static function nullOrEmail($value, $message = '');
    public static function allEmail($value, $message = '');
    public static function nullOrUniqueValues($values, $message = '');
    public static function allUniqueValues($values, $message = '');
    public static function nullOrEq($value, $expect, $message = '');
    public static function allEq($value, $expect, $message = '');
    public static function nullOrNotEq($value, $expect, $message = '');
    public static function allNotEq($value, $expect, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrSame($value, $expect, $message = '');
    /**
    @psalm-pure
    */
    public static function allSame($value, $expect, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrNotSame($value, $expect, $message = '');
    /**
    @psalm-pure
    */
    public static function allNotSame($value, $expect, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrGreaterThan($value, $limit, $message = '');
    /**
    @psalm-pure
    */
    public static function allGreaterThan($value, $limit, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrGreaterThanEq($value, $limit, $message = '');
    /**
    @psalm-pure
    */
    public static function allGreaterThanEq($value, $limit, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrLessThan($value, $limit, $message = '');
    /**
    @psalm-pure
    */
    public static function allLessThan($value, $limit, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrLessThanEq($value, $limit, $message = '');
    /**
    @psalm-pure
    */
    public static function allLessThanEq($value, $limit, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrRange($value, $min, $max, $message = '');
    /**
    @psalm-pure
    */
    public static function allRange($value, $min, $max, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrOneOf($value, $values, $message = '');
    /**
    @psalm-pure
    */
    public static function allOneOf($value, $values, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrInArray($value, $values, $message = '');
    /**
    @psalm-pure
    */
    public static function allInArray($value, $values, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrContains($value, $subString, $message = '');
    /**
    @psalm-pure
    */
    public static function allContains($value, $subString, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrNotContains($value, $subString, $message = '');
    /**
    @psalm-pure
    */
    public static function allNotContains($value, $subString, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrNotWhitespaceOnly($value, $message = '');
    /**
    @psalm-pure
    */
    public static function allNotWhitespaceOnly($value, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrStartsWith($value, $prefix, $message = '');
    /**
    @psalm-pure
    */
    public static function allStartsWith($value, $prefix, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrNotStartsWith($value, $prefix, $message = '');
    /**
    @psalm-pure
    */
    public static function allNotStartsWith($value, $prefix, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrStartsWithLetter($value, $message = '');
    /**
    @psalm-pure
    */
    public static function allStartsWithLetter($value, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrEndsWith($value, $suffix, $message = '');
    /**
    @psalm-pure
    */
    public static function allEndsWith($value, $suffix, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrNotEndsWith($value, $suffix, $message = '');
    /**
    @psalm-pure
    */
    public static function allNotEndsWith($value, $suffix, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrRegex($value, $pattern, $message = '');
    /**
    @psalm-pure
    */
    public static function allRegex($value, $pattern, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrNotRegex($value, $pattern, $message = '');
    /**
    @psalm-pure
    */
    public static function allNotRegex($value, $pattern, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrUnicodeLetters($value, $message = '');
    /**
    @psalm-pure
    */
    public static function allUnicodeLetters($value, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrAlpha($value, $message = '');
    /**
    @psalm-pure
    */
    public static function allAlpha($value, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrDigits($value, $message = '');
    /**
    @psalm-pure
    */
    public static function allDigits($value, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrAlnum($value, $message = '');
    /**
    @psalm-pure
    */
    public static function allAlnum($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrLower($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allLower($value, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrUpper($value, $message = '');
    /**
    @psalm-pure
    */
    public static function allUpper($value, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrLength($value, $length, $message = '');
    /**
    @psalm-pure
    */
    public static function allLength($value, $length, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrMinLength($value, $min, $message = '');
    /**
    @psalm-pure
    */
    public static function allMinLength($value, $min, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrMaxLength($value, $max, $message = '');
    /**
    @psalm-pure
    */
    public static function allMaxLength($value, $max, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrLengthBetween($value, $min, $max, $message = '');
    /**
    @psalm-pure
    */
    public static function allLengthBetween($value, $min, $max, $message = '');
    public static function nullOrFileExists($value, $message = '');
    public static function allFileExists($value, $message = '');
    public static function nullOrFile($value, $message = '');
    public static function allFile($value, $message = '');
    public static function nullOrDirectory($value, $message = '');
    public static function allDirectory($value, $message = '');
    public static function nullOrReadable($value, $message = '');
    public static function allReadable($value, $message = '');
    public static function nullOrWritable($value, $message = '');
    public static function allWritable($value, $message = '');
    /**
    @psalm-assert
    */
    public static function nullOrClassExists($value, $message = '');
    /**
    @psalm-assert
    */
    public static function allClassExists($value, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function nullOrSubclassOf($value, $class, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function allSubclassOf($value, $class, $message = '');
    /**
    @psalm-assert
    */
    public static function nullOrInterfaceExists($value, $message = '');
    /**
    @psalm-assert
    */
    public static function allInterfaceExists($value, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function nullOrImplementsInterface($value, $interface, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function allImplementsInterface($value, $interface, $message = '');
    /**
    @psalm-pure
    @psalm-param
    */
    public static function nullOrPropertyExists($classOrObject, $property, $message = '');
    /**
    @psalm-pure
    @psalm-param
    */
    public static function allPropertyExists($classOrObject, $property, $message = '');
    /**
    @psalm-pure
    @psalm-param
    */
    public static function nullOrPropertyNotExists($classOrObject, $property, $message = '');
    /**
    @psalm-pure
    @psalm-param
    */
    public static function allPropertyNotExists($classOrObject, $property, $message = '');
    /**
    @psalm-pure
    @psalm-param
    */
    public static function nullOrMethodExists($classOrObject, $method, $message = '');
    /**
    @psalm-pure
    @psalm-param
    */
    public static function allMethodExists($classOrObject, $method, $message = '');
    /**
    @psalm-pure
    @psalm-param
    */
    public static function nullOrMethodNotExists($classOrObject, $method, $message = '');
    /**
    @psalm-pure
    @psalm-param
    */
    public static function allMethodNotExists($classOrObject, $method, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrKeyExists($array, $key, $message = '');
    /**
    @psalm-pure
    */
    public static function allKeyExists($array, $key, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrKeyNotExists($array, $key, $message = '');
    /**
    @psalm-pure
    */
    public static function allKeyNotExists($array, $key, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrValidArrayKey($value, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allValidArrayKey($value, $message = '');
    public static function nullOrCount($array, $number, $message = '');
    public static function allCount($array, $number, $message = '');
    public static function nullOrMinCount($array, $min, $message = '');
    public static function allMinCount($array, $min, $message = '');
    public static function nullOrMaxCount($array, $max, $message = '');
    public static function allMaxCount($array, $max, $message = '');
    public static function nullOrCountBetween($array, $min, $max, $message = '');
    public static function allCountBetween($array, $min, $max, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrIsList($array, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allIsList($array, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function nullOrIsNonEmptyList($array, $message = '');
    /**
    @psalm-pure
    @psalm-assert
    */
    public static function allIsNonEmptyList($array, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function nullOrIsMap($array, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    @psalm-assert
    */
    public static function allIsMap($array, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    */
    public static function nullOrIsNonEmptyMap($array, $message = '');
    /**
    @psalm-pure
    @psalm-template
    @psalm-param
    */
    public static function allIsNonEmptyMap($array, $message = '');
    /**
    @psalm-pure
    */
    public static function nullOrUuid($value, $message = '');
    /**
    @psalm-pure
    */
    public static function allUuid($value, $message = '');
    /**
    @psalm-param
    */
    public static function nullOrThrows($expression, $class = 'Exception', $message = '');
    /**
    @psalm-param
    */
    public static function allThrows($expression, $class = 'Exception', $message = '');
}
