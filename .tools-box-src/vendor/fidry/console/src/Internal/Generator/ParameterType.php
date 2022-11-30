<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Internal\Generator;

final class ParameterType
{
    public const ARGUMENT = 'ARGUMENT';
    public const OPTION = 'OPTION';
    public const ALL = [self::ARGUMENT, self::OPTION];
    private function __construct()
    {
    }
}
