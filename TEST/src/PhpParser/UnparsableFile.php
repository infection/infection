<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser;

use RuntimeException;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use Throwable;
final class UnparsableFile extends RuntimeException
{
    public static function fromInvalidFile(string $filePath, Throwable $original) : self
    {
        return new self(sprintf('Could not parse the file "%s". Check if it is a valid PHP file', $filePath), 0, $original);
    }
}
