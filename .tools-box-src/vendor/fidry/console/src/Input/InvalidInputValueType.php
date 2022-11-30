<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Fidry\Console\Input;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\InvalidArgumentException as ConsoleInvalidArgumentException;
use _HumbugBoxb47773b41c19\Webmozart\Assert\InvalidArgumentException as AssertInvalidArgumentException;
use function sprintf;
final class InvalidInputValueType extends ConsoleInvalidArgumentException
{
    public static function fromAssert(AssertInvalidArgumentException $exception, string $inputLabel) : self
    {
        return new self(sprintf('%s for %s.', $exception->getMessage(), $inputLabel), (int) $exception->getCode(), $exception);
    }
    public static function withErrorMessage(self $exception, string $errorMessage) : self
    {
        return new self(sprintf($errorMessage, $exception->getMessage()), (int) $exception->getCode(), $exception);
    }
}
