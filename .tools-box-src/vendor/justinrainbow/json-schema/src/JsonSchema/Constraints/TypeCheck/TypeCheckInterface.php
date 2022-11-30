<?php

namespace _HumbugBoxb47773b41c19\JsonSchema\Constraints\TypeCheck;

interface TypeCheckInterface
{
    public static function isObject($value);
    public static function isArray($value);
    public static function propertyGet($value, $property);
    public static function propertySet(&$value, $property, $data);
    public static function propertyExists($value, $property);
    public static function propertyCount($value);
}
