<?php

namespace _HumbugBox9658796bb9f0\Psr\Log;

class NullLogger extends AbstractLogger
{
    public function log($level, $message, array $context = array())
    {
    }
}
