<?php

namespace _HumbugBoxb47773b41c19\Psr\Log;

trait LoggerAwareTrait
{
    protected ?LoggerInterface $logger = null;
    public function setLogger(LoggerInterface $logger) : void
    {
        $this->logger = $logger;
    }
}
