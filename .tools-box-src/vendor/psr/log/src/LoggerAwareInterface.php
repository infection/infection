<?php

namespace _HumbugBoxb47773b41c19\Psr\Log;

interface LoggerAwareInterface
{
    public function setLogger(LoggerInterface $logger) : void;
}
