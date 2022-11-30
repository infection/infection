<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\XmlReport;

use UnexpectedValueException;
final class NoLineExecuted extends UnexpectedValueException
{
    public static function create() : self
    {
        return new self(<<<'MSG'
No line of code was executed during tests. This could be due to "@covers" annotations or your
PHPUnit filters not being set up correctly.
MSG
);
    }
}
