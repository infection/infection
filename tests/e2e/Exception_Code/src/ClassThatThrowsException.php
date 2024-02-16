<?php

declare(strict_types=1);

namespace ExceptionCode;

final class ClassThatThrowsException
{
    public static function someMethod(): void
    {
        throw new SomeException();
    }
}
