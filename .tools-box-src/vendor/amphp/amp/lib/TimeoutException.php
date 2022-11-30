<?php

namespace _HumbugBoxb47773b41c19\Amp;

class TimeoutException extends \Exception
{
    public function __construct(string $message = "Operation timed out")
    {
        parent::__construct($message);
    }
}
