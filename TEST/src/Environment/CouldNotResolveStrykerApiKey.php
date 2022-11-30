<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Environment;

use function implode;
use RuntimeException;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
final class CouldNotResolveStrykerApiKey extends RuntimeException
{
    public static function from(string ...$names) : self
    {
        return new self(sprintf('The Stryker API key needs to be configured using one of the environment variables "%s", but could not find any of these.', implode('" or "', $names)));
    }
}
