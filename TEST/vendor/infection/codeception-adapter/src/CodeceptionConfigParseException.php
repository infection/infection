<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Codeception;

use Exception;
use function sprintf;
final class CodeceptionConfigParseException extends Exception
{
    public static function fromPath(string $configPath, Exception $originalException) : self
    {
        return new self(sprintf("Error loading Yaml config from '%s'\n \n%s", $configPath, $originalException->getMessage()));
    }
}
