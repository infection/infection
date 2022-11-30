<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec;

use RuntimeException;
final class FinderException extends RuntimeException
{
    public static function phpExecutableNotFound() : self
    {
        return new self('Unable to locate the PHP executable on the local system. Please report this issue, and include details about your setup.');
    }
}
