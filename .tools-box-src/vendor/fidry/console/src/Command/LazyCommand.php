<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Command;

interface LazyCommand extends Command
{
    public static function getName() : string;
    public static function getDescription() : string;
}
