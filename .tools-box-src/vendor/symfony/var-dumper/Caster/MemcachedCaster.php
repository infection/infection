<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Caster;

use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Stub;
class MemcachedCaster
{
    private static array $optionConstants;
    private static array $defaultOptions;
    public static function castMemcached(\Memcached $c, array $a, Stub $stub, bool $isNested)
    {
        $a += [Caster::PREFIX_VIRTUAL . 'servers' => $c->getServerList(), Caster::PREFIX_VIRTUAL . 'options' => new EnumStub(self::getNonDefaultOptions($c))];
        return $a;
    }
    private static function getNonDefaultOptions(\Memcached $c) : array
    {
        self::$defaultOptions = self::$defaultOptions ?? self::discoverDefaultOptions();
        self::$optionConstants = self::$optionConstants ?? self::getOptionConstants();
        $nonDefaultOptions = [];
        foreach (self::$optionConstants as $constantKey => $value) {
            if (self::$defaultOptions[$constantKey] !== ($option = $c->getOption($value))) {
                $nonDefaultOptions[$constantKey] = $option;
            }
        }
        return $nonDefaultOptions;
    }
    private static function discoverDefaultOptions() : array
    {
        $defaultMemcached = new \Memcached();
        $defaultMemcached->addServer('127.0.0.1', 11211);
        $defaultOptions = [];
        self::$optionConstants = self::$optionConstants ?? self::getOptionConstants();
        foreach (self::$optionConstants as $constantKey => $value) {
            $defaultOptions[$constantKey] = $defaultMemcached->getOption($value);
        }
        return $defaultOptions;
    }
    private static function getOptionConstants() : array
    {
        $reflectedMemcached = new \ReflectionClass(\Memcached::class);
        $optionConstants = [];
        foreach ($reflectedMemcached->getConstants() as $constantKey => $value) {
            if (\str_starts_with($constantKey, 'OPT_')) {
                $optionConstants[$constantKey] = $value;
            }
        }
        return $optionConstants;
    }
}
