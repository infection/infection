<?php

declare(strict_types=1);

namespace Infection\Configuration\RawConfiguration;

use function sprintf;
use Throwable;
use UnexpectedValueException;

final class InvalidFile extends UnexpectedValueException
{
    public static function createForFileNotFound(RawConfiguration $config): self
    {
        return new self(sprintf(
            'The file "%s" could not be found or is not a file.',
            $config->getPath()
        ));
    }

    public static function createForFileNotReadable(RawConfiguration $config): self
    {
        return new self(sprintf(
            'The file "%s" is not readable.',
            $config->getPath()
        ));
    }

    public static function createForCouldNotRetrieveFileContents(RawConfiguration $config): self
    {
        return new self(sprintf(
            'Could not retrieve the contents of the file "%s".',
            $config->getPath()
        ));
    }

    public static function createForInvalidJson(
        RawConfiguration $config,
        string $error,
        Throwable $previous
    ): self
    {
        return new self(
            sprintf(
                'Could not parse the JSON file "%s": %s',
                $config->getPath(),
                $error
            ),
            0,
            $previous
        );
    }
}