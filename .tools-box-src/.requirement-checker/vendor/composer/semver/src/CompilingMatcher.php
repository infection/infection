<?php

namespace HumbugBox420\Composer\Semver;

use HumbugBox420\Composer\Semver\Constraint\Constraint;
use HumbugBox420\Composer\Semver\Constraint\ConstraintInterface;
class CompilingMatcher
{
    /**
    @phpstan-var
    */
    private static $compiledCheckerCache = array();
    /**
    @phpstan-var
    */
    private static $resultCache = array();
    private static $enabled;
    /**
    @phpstan-var
    */
    private static $transOpInt = array(Constraint::OP_EQ => Constraint::STR_OP_EQ, Constraint::OP_LT => Constraint::STR_OP_LT, Constraint::OP_LE => Constraint::STR_OP_LE, Constraint::OP_GT => Constraint::STR_OP_GT, Constraint::OP_GE => Constraint::STR_OP_GE, Constraint::OP_NE => Constraint::STR_OP_NE);
    public static function clear()
    {
        self::$resultCache = array();
        self::$compiledCheckerCache = array();
    }
    /**
    @phpstan-param
    */
    public static function match(ConstraintInterface $constraint, $operator, $version)
    {
        $resultCacheKey = $operator . $constraint . ';' . $version;
        if (isset(self::$resultCache[$resultCacheKey])) {
            return self::$resultCache[$resultCacheKey];
        }
        if (self::$enabled === null) {
            self::$enabled = !\in_array('eval', \explode(',', (string) \ini_get('disable_functions')), \true);
        }
        if (!self::$enabled) {
            return self::$resultCache[$resultCacheKey] = $constraint->matches(new Constraint(self::$transOpInt[$operator], $version));
        }
        $cacheKey = $operator . $constraint;
        if (!isset(self::$compiledCheckerCache[$cacheKey])) {
            $code = $constraint->compile($operator);
            self::$compiledCheckerCache[$cacheKey] = $function = eval('return function($v, $b){return ' . $code . ';};');
        } else {
            $function = self::$compiledCheckerCache[$cacheKey];
        }
        return self::$resultCache[$resultCacheKey] = $function($version, \strpos($version, 'dev-') === 0);
    }
}
