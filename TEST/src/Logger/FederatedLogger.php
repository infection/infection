<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Logger;

final class FederatedLogger implements MutationTestingResultsLogger
{
    private array $loggers;
    public function __construct(MutationTestingResultsLogger ...$loggers)
    {
        $this->loggers = $loggers;
    }
    public function log() : void
    {
        foreach ($this->loggers as $logger) {
            $logger->log();
        }
    }
    public function getLoggers() : array
    {
        return $this->loggers;
    }
}
