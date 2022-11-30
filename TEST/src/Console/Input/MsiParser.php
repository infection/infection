<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Console\Input;

use function count;
use function explode;
use _HumbugBox9658796bb9f0\Infection\CannotBeInstantiated;
use function max;
use const PHP_ROUND_HALF_UP;
use function round;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use function strlen;
use function trim;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class MsiParser
{
    use CannotBeInstantiated;
    public const DEFAULT_PRECISION = 2;
    public static function detectPrecision(?string ...$values) : int
    {
        $precisions = [self::DEFAULT_PRECISION];
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }
            $valueParts = explode('.', $value);
            if (count($valueParts) !== 2) {
                continue;
            }
            $precisions[] = strlen($valueParts[1]);
        }
        return (int) max($precisions);
    }
    public static function parse(?string $value, int $precision, string $optionName) : ?float
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        Assert::numeric($value, sprintf('Expected %s to be a float. Got "%s"', $optionName, $value));
        $roundedValue = round((float) $value, $precision, PHP_ROUND_HALF_UP);
        Assert::range($roundedValue, 0, 100, sprintf('Expected %s to be an element of [0;100]. Got %%s', $optionName));
        return $roundedValue;
    }
}
