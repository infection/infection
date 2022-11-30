<?php

namespace HumbugBox420\Composer\Semver;

use HumbugBox420\Composer\Semver\Constraint\Constraint;
class Comparator
{
    public static function greaterThan($version1, $version2)
    {
        return self::compare($version1, '>', $version2);
    }
    public static function greaterThanOrEqualTo($version1, $version2)
    {
        return self::compare($version1, '>=', $version2);
    }
    public static function lessThan($version1, $version2)
    {
        return self::compare($version1, '<', $version2);
    }
    public static function lessThanOrEqualTo($version1, $version2)
    {
        return self::compare($version1, '<=', $version2);
    }
    public static function equalTo($version1, $version2)
    {
        return self::compare($version1, '==', $version2);
    }
    public static function notEqualTo($version1, $version2)
    {
        return self::compare($version1, '!=', $version2);
    }
    /**
    @phpstan-param
    */
    public static function compare($version1, $operator, $version2)
    {
        $constraint = new Constraint($operator, $version2);
        return $constraint->matchSpecific(new Constraint('==', $version1), \true);
    }
}
