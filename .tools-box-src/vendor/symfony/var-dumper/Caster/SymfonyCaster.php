<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Caster;

use _HumbugBoxb47773b41c19\Symfony\Component\HttpFoundation\Request;
use _HumbugBoxb47773b41c19\Symfony\Component\Uid\Ulid;
use _HumbugBoxb47773b41c19\Symfony\Component\Uid\Uuid;
use _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner\Stub;
class SymfonyCaster
{
    private const REQUEST_GETTERS = ['pathInfo' => 'getPathInfo', 'requestUri' => 'getRequestUri', 'baseUrl' => 'getBaseUrl', 'basePath' => 'getBasePath', 'method' => 'getMethod', 'format' => 'getRequestFormat'];
    public static function castRequest(Request $request, array $a, Stub $stub, bool $isNested)
    {
        $clone = null;
        foreach (self::REQUEST_GETTERS as $prop => $getter) {
            $key = Caster::PREFIX_PROTECTED . $prop;
            if (\array_key_exists($key, $a) && null === $a[$key]) {
                if (null === $clone) {
                    $clone = clone $request;
                }
                $a[Caster::PREFIX_VIRTUAL . $prop] = $clone->{$getter}();
            }
        }
        return $a;
    }
    public static function castHttpClient($client, array $a, Stub $stub, bool $isNested)
    {
        $multiKey = \sprintf("\x00%s\x00multi", \get_class($client));
        if (isset($a[$multiKey])) {
            $a[$multiKey] = new CutStub($a[$multiKey]);
        }
        return $a;
    }
    public static function castHttpClientResponse($response, array $a, Stub $stub, bool $isNested)
    {
        $stub->cut += \count($a);
        $a = [];
        foreach ($response->getInfo() as $k => $v) {
            $a[Caster::PREFIX_VIRTUAL . $k] = $v;
        }
        return $a;
    }
    public static function castUuid(Uuid $uuid, array $a, Stub $stub, bool $isNested)
    {
        $a[Caster::PREFIX_VIRTUAL . 'toBase58'] = $uuid->toBase58();
        $a[Caster::PREFIX_VIRTUAL . 'toBase32'] = $uuid->toBase32();
        if (\method_exists($uuid, 'getDateTime')) {
            $a[Caster::PREFIX_VIRTUAL . 'time'] = $uuid->getDateTime()->format('Y-m-d H:i:s.u \\U\\T\\C');
        }
        return $a;
    }
    public static function castUlid(Ulid $ulid, array $a, Stub $stub, bool $isNested)
    {
        $a[Caster::PREFIX_VIRTUAL . 'toBase58'] = $ulid->toBase58();
        $a[Caster::PREFIX_VIRTUAL . 'toRfc4122'] = $ulid->toRfc4122();
        if (\method_exists($ulid, 'getDateTime')) {
            $a[Caster::PREFIX_VIRTUAL . 'time'] = $ulid->getDateTime()->format('Y-m-d H:i:s.v \\U\\T\\C');
        }
        return $a;
    }
}
