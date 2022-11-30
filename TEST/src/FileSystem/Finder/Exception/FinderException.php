<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\FileSystem\Finder\Exception;

use RuntimeException;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
final class FinderException extends RuntimeException
{
    public static function composerNotFound() : self
    {
        return new self('Unable to locate a Composer executable on local system. Ensure that Composer is installed and available.');
    }
    public static function phpExecutableNotFound() : self
    {
        return new self('Unable to locate the PHP executable on the local system. Please report this issue, and include details about your setup.');
    }
    public static function testFrameworkNotFound(string $testFrameworkName) : self
    {
        return new self(sprintf('Unable to locate a %s executable on local system. Ensure that %s is installed and available.', $testFrameworkName, $testFrameworkName));
    }
    public static function testCustomPathDoesNotExist(string $testFrameworkName, string $customPath) : self
    {
        return new self(sprintf('The custom path to %s was set as "%s" but this file did not exist.', $testFrameworkName, $customPath));
    }
}
