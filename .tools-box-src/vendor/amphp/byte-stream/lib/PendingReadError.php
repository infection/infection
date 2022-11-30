<?php

namespace _HumbugBoxb47773b41c19\Amp\ByteStream;

final class PendingReadError extends \Error
{
    public function __construct(string $message = "The previous read operation must complete before read can be called again", int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
