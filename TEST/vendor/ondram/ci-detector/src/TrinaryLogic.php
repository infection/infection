<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\OndraM\CiDetector;

final class TrinaryLogic
{
    private const YES = 1;
    private const MAYBE = 0;
    private const NO = -1;
    private static $registry = [];
    private $value;
    private function __construct(int $value)
    {
        $this->value = $value;
    }
    public static function createMaybe() : self
    {
        return self::create(self::MAYBE);
    }
    public static function createFromBoolean(bool $value) : self
    {
        return self::create($value ? self::YES : self::NO);
    }
    private static function create(int $value) : self
    {
        return self::$registry[$value] = self::$registry[$value] ?? new self($value);
    }
    public function yes() : bool
    {
        return $this->value === self::YES;
    }
    public function maybe() : bool
    {
        return $this->value === self::MAYBE;
    }
    public function no() : bool
    {
        return $this->value === self::NO;
    }
    public function describe() : string
    {
        static $labels = [self::NO => 'No', self::MAYBE => 'Maybe', self::YES => 'Yes'];
        return $labels[$this->value];
    }
}
