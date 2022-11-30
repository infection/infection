<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner;

class Stub
{
    public const TYPE_REF = 1;
    public const TYPE_STRING = 2;
    public const TYPE_ARRAY = 3;
    public const TYPE_OBJECT = 4;
    public const TYPE_RESOURCE = 5;
    public const STRING_BINARY = 1;
    public const STRING_UTF8 = 2;
    public const ARRAY_ASSOC = 1;
    public const ARRAY_INDEXED = 2;
    public $type = self::TYPE_REF;
    public $class = '';
    public $value;
    public $cut = 0;
    public $handle = 0;
    public $refCount = 0;
    public $position = 0;
    public $attr = [];
    private static array $defaultProperties = [];
    public function __sleep() : array
    {
        $properties = [];
        if (!isset(self::$defaultProperties[$c = static::class])) {
            self::$defaultProperties[$c] = \get_class_vars($c);
            foreach ((new \ReflectionClass($c))->getStaticProperties() as $k => $v) {
                unset(self::$defaultProperties[$c][$k]);
            }
        }
        foreach (self::$defaultProperties[$c] as $k => $v) {
            if ($this->{$k} !== $v) {
                $properties[] = $k;
            }
        }
        return $properties;
    }
}
