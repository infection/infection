<?php

namespace Decimal;

final class Decimal implements \JsonSerializable
{



public const ROUND_UP = 0; 
public const ROUND_DOWN = 0; 
public const ROUND_CEILING = 0; 
public const ROUND_FLOOR = 0; 
public const ROUND_HALF_UP = 0; 
public const ROUND_HALF_DOWN = 0; 
public const ROUND_HALF_EVEN = 0; 
public const ROUND_HALF_ODD = 0; 
public const ROUND_TRUNCATE = 0; 
public const DEFAULT_ROUNDING = Decimal::ROUND_HALF_EVEN;
public const DEFAULT_PRECISION = 28;
public const MIN_PRECISION = 1;
public const MAX_PRECISION = 0; 














public function __construct($value, int $precision = Decimal::DEFAULT_PRECISION) {}


















public static function sum($values, int $precision = Decimal::DEFAULT_PRECISION): Decimal {}



















public static function avg($values, int $precision = Decimal::DEFAULT_PRECISION): Decimal {}









public function copy(?int $precision = null): Decimal {}















public function add($value): Decimal {}















public function sub($value): Decimal {}















public function mul($value): Decimal {}

















public function div($value): Decimal {}




















public function mod($value): Decimal {}















public function rem($value): Decimal {}















public function pow($exponent): Decimal {}









public function ln(): Decimal {}







public function exp(): Decimal {}







public function log10(): Decimal {}







public function sqrt(): Decimal {}






public function floor(): Decimal {}






public function ceil(): Decimal {}






public function truncate(): Decimal {}













public function round(int $places = 0, int $mode = Decimal::DEFAULT_ROUNDING): Decimal {}










public function shift(int $places): Decimal {}






public function trim(): Decimal {}






public function precision(): int {}






public function signum(): int {}







public function parity(): int {}






public function abs(): Decimal {}






public function negate(): Decimal {}




public function isEven(): bool {}




public function isOdd(): bool {}




public function isPositive(): bool {}




public function isNegative(): bool {}




public function isNaN(): bool {}




public function isInf(): bool {}





public function isInteger(): bool {}




public function isZero(): bool {}










public function toFixed(int $places = 0, bool $commas = false, int $rounding = Decimal::DEFAULT_ROUNDING): string {}














public function toString(): string {}










public function toInt(): int {}












public function toFloat(): float {}











public function equals($other): bool {}












public function compareTo($other): int {}









public function __toString(): string {}










public function jsonSerialize() {}
}
