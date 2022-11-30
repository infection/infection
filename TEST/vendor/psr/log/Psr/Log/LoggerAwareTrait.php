<?php

namespace _HumbugBox9658796bb9f0\Psr\Log;

trait LoggerAwareTrait
{
    protected $logger;
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
