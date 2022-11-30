<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser;

use UnexpectedValueException;
final class UnexpectedParsingScenario extends UnexpectedValueException
{
    public static function create() : self
    {
        return new self('Unexpected case. Please report it.');
    }
}
