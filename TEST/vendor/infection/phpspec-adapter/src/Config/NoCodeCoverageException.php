<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpSpec\Config;

use Exception;
use function sprintf;
final class NoCodeCoverageException extends Exception
{
    public static function fromTestFramework(string $framework) : self
    {
        return new self(sprintf('No code coverage Extension detected for %s. %sWithout code coverage, running Infection is not useful.', $framework, "\n"));
    }
}
