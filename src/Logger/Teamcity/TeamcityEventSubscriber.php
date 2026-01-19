<?php

declare(strict_types=1);

namespace Infection\Logger\Teamcity;

use Infection\Event\MutationGenerationWasStarted;
use Infection\Event\MutationTestingWasStarted;
use Infection\Event\Subscriber\EventSubscriber;

final class TeamcityEventSubscriber implements EventSubscriber
{
    public function onMutationGenerationWasStarted(MutationGenerationWasStarted $event): void
    {

    }

    public function onMutationTestingWasStarted(MutationTestingWasStarted $event): void
    {

    }
}
