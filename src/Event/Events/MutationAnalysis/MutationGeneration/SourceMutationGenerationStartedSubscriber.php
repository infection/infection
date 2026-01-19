<?php

declare(strict_types=1);

namespace Infection\Event\Events\MutationAnalysis\MutationGeneration;

use Infection\Event\Subscriber\EventSubscriber;

/**
 * @internal
 */
interface SourceMutationGenerationStartedSubscriber extends EventSubscriber
{
    public function onSourceMutationGenerationStarted(SourceMutationGenerationStarted $event): void;
}
