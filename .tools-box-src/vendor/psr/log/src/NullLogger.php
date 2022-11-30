<?php

namespace _HumbugBoxb47773b41c19\Psr\Log;

class NullLogger extends AbstractLogger
{
    public function log($level, string|\Stringable $message, array $context = []) : void
    {
    }
}
