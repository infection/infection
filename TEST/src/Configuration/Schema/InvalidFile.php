<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Configuration\Schema;

use function _HumbugBox9658796bb9f0\Safe\sprintf;
use Throwable;
use UnexpectedValueException;
final class InvalidFile extends UnexpectedValueException
{
    public static function createForFileNotFound(SchemaConfigurationFile $config) : self
    {
        return new self(sprintf('The file "%s" could not be found or is not a file.', $config->getPath()));
    }
    public static function createForFileNotReadable(SchemaConfigurationFile $config) : self
    {
        return new self(sprintf('The file "%s" is not readable.', $config->getPath()));
    }
    public static function createForCouldNotRetrieveFileContents(SchemaConfigurationFile $config) : self
    {
        return new self(sprintf('Could not retrieve the contents of the file "%s".', $config->getPath()));
    }
    public static function createForInvalidJson(SchemaConfigurationFile $config, string $error, Throwable $previous) : self
    {
        return new self(sprintf('Could not parse the JSON file "%s": %s', $config->getPath(), $error), 0, $previous);
    }
}
