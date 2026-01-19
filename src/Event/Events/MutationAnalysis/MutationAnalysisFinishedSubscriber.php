<?php

declare(strict_types=1);

namespace Infection\Event\Events\MutationAnalysis;

use Infection\Event\Events\SourceCollection\SourceCollectionFinished;
use Infection\Event\Subscriber\EventSubscriber;

/**
 * @internal
 */
interface MutationAnalysisFinishedSubscriber extends EventSubscriber
{
    public function onMutationAnalysisFinished(MutationAnalysisFinished $event): void;
}
