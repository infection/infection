<?php

declare(strict_types=1);

namespace Infection\Event\Events\MutationAnalysis\MutationEvaluation;

use Infection\Event\Subscriber\EventSubscriber;

/**
 * @internal
 */
interface SourceMutationEvaluationFinishedSubscriber extends EventSubscriber
{
    public function onSourceMutationEvaluationFinished(SourceMutationEvaluationFinished $event): void;
}
