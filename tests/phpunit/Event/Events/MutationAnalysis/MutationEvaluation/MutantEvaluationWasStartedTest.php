<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Events\MutationAnalysis\MutationEvaluation;

use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutantEvaluationWasStarted;
use Infection\Tests\Mutation\MutationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MutantEvaluationWasStarted::class)]
final class MutantEvaluationWasStartedTest extends TestCase
{
    public function test_it_exposes_the_mutation(): void
    {
        $mutation = MutationBuilder::withMinimalTestData()->build();
        $event = new MutantEvaluationWasStarted($mutation);

        $this->assertSame($mutation, $event->mutation);
    }
}
