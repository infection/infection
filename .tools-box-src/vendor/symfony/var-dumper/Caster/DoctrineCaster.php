<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Caster;

use _HumbugBoxb47773b41c19\Doctrine\Common\Proxy\Proxy as CommonProxy;
use _HumbugBoxb47773b41c19\Doctrine\ORM\PersistentCollection;
use _HumbugBoxb47773b41c19\Doctrine\ORM\Proxy\Proxy as OrmProxy;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Stub;
class DoctrineCaster
{
    public static function castCommonProxy(CommonProxy $proxy, array $a, Stub $stub, bool $isNested)
    {
        foreach (['__cloner__', '__initializer__'] as $k) {
            if (\array_key_exists($k, $a)) {
                unset($a[$k]);
                ++$stub->cut;
            }
        }
        return $a;
    }
    public static function castOrmProxy(OrmProxy $proxy, array $a, Stub $stub, bool $isNested)
    {
        foreach (['_entityPersister', '_identifier'] as $k) {
            if (\array_key_exists($k = "\x00Doctrine\\ORM\\Proxy\\Proxy\x00" . $k, $a)) {
                unset($a[$k]);
                ++$stub->cut;
            }
        }
        return $a;
    }
    public static function castPersistentCollection(PersistentCollection $coll, array $a, Stub $stub, bool $isNested)
    {
        foreach (['snapshot', 'association', 'typeClass'] as $k) {
            if (\array_key_exists($k = "\x00Doctrine\\ORM\\PersistentCollection\x00" . $k, $a)) {
                $a[$k] = new CutStub($a[$k]);
            }
        }
        return $a;
    }
}
