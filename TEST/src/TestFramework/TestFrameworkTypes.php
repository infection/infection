<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework;

final class TestFrameworkTypes
{
    public const PHPUNIT = 'phpunit';
    public const PEST = 'pest';
    public const PHPSPEC = 'phpspec';
    public const CODECEPTION = 'codeception';
    public const TYPES = [self::PEST, self::PHPUNIT, self::PHPSPEC, self::CODECEPTION];
}
