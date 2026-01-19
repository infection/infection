<?php

declare(strict_types=1);

namespace Infection\Event\Events\MutationAnalysis\MutationGeneration;

use Infection\Event\Subscriber\EventSubscriber;

/**
 * @internal
 */
interface SourceMutationGenerationFinishedSubscriber extends EventSubscriber
{
    public function onSourceMutationGenerationFinished(SourceMutationGenerationFinished $event): void;
}
