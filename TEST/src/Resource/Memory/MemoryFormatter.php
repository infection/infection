<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Resource\Memory;

use function log;
use function number_format;
use function round;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class MemoryFormatter
{
    private const UNITS = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    public function toHumanReadableString(float $bytes) : string
    {
        Assert::greaterThanEq($bytes, 0.0, 'Expected a positive or null amount of bytes. Got: %s');
        $power = $bytes > 0 ? (int) round(log($bytes, 1023)) : 0;
        return sprintf('%s%s', number_format($bytes / 1024 ** $power, 2, '.', ','), self::UNITS[$power]);
    }
}
