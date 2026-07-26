<?php

declare(strict_types=1);

namespace ExceptionCode;

use Exception;

final class SomeException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'some message',
            1337
        );
    }
}
