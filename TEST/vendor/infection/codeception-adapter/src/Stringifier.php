<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Codeception;

use function implode;
use function sprintf;
use _HumbugBox9658796bb9f0\Webmozart\Assert\Assert;
final class Stringifier
{
    private function __construct()
    {
    }
    public static function stringifyBoolean(bool $value) : string
    {
        return $value ? 'true' : 'false';
    }
    public static function stringifyArray(array $arrayOfStrings) : string
    {
        Assert::allString($arrayOfStrings);
        return sprintf('[%s]', implode(',', $arrayOfStrings));
    }
}
