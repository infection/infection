<?php

namespace _HumbugBoxb47773b41c19\Composer\Semver;

use _HumbugBoxb47773b41c19\Composer\Semver\Constraint\Constraint;
class Interval
{
    private $start;
    private $end;
    public function __construct(Constraint $start, Constraint $end)
    {
        $this->start = $start;
        $this->end = $end;
    }
    public function getStart()
    {
        return $this->start;
    }
    public function getEnd()
    {
        return $this->end;
    }
    public static function fromZero()
    {
        static $zero;
        if (null === $zero) {
            $zero = new Constraint('>=', '0.0.0.0-dev');
        }
        return $zero;
    }
    public static function untilPositiveInfinity()
    {
        static $positiveInfinity;
        if (null === $positiveInfinity) {
            $positiveInfinity = new Constraint('<', \PHP_INT_MAX . '.0.0.0');
        }
        return $positiveInfinity;
    }
    public static function any()
    {
        return new self(self::fromZero(), self::untilPositiveInfinity());
    }
    public static function anyDev()
    {
        return array('names' => array(), 'exclude' => \true);
    }
    public static function noDev()
    {
        return array('names' => array(), 'exclude' => \false);
    }
}
