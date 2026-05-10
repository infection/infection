<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Events\MutationAnalysis\MutationEvaluation;

use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationWasFinished;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MutationEvaluationWasFinished::class)]
final class MutationEvaluationWasFinishedTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(MutationEvaluationWasFinished::class, new MutationEvaluationWasFinished());
    }
}
