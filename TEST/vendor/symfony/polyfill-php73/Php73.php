<?php

namespace _HumbugBox9658796bb9f0\Symfony\Polyfill\Php73;

final class Php73
{
    public static $startAt = 1533462603;
    public static function hrtime($asNum = \false)
    {
        $ns = \microtime(\false);
        $s = \substr($ns, 11) - self::$startAt;
        $ns = 1000000000.0 * (float) $ns;
        if ($asNum) {
            $ns += $s * 1000000000.0;
            return \PHP_INT_SIZE === 4 ? $ns : (int) $ns;
        }
        return [$s, (int) $ns];
    }
}
