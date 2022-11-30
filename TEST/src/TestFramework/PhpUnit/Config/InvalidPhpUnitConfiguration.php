<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\PhpUnit\Config;

use const PHP_EOL;
use RuntimeException;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
final class InvalidPhpUnitConfiguration extends RuntimeException
{
    public static function byRootNode(string $configPath) : self
    {
        return new self(sprintf('The file "%s" is not a valid PHPUnit configuration file', $configPath));
    }
    public static function byXsdSchema(string $configPath, string $libXmlErrorsString) : self
    {
        return new self(sprintf('The file "%s" does not pass the XSD schema validation.%s%s', $configPath, PHP_EOL, $libXmlErrorsString));
    }
}
