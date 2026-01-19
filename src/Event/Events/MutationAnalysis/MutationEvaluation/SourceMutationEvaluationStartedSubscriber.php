<?php

declare(strict_types=1);

namespace Infection\Event\Events\MutationAnalysis\MutationEvaluation;

use Infection\Event\Subscriber\EventSubscriber;

/**
 * @internal
 */
interface SourceMutationEvaluationStartedSubscriber extends EventSubscriber
{
    public function onSourceMutationEvaluationStarted(SourceMutationEvaluationStarted $event): void;
}
