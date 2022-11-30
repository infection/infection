<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Infection\Event\MutationTestingWasFinished;
use _HumbugBox9658796bb9f0\Infection\Logger\MutationTestingResultsLogger;
final class MutationTestingResultsLoggerSubscriber implements EventSubscriber
{
    public function __construct(private MutationTestingResultsLogger $logger)
    {
    }
    public function onMutationTestingWasFinished(MutationTestingWasFinished $event) : void
    {
        $this->logger->log();
    }
}
