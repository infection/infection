<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Events\MutationAnalysis\MutationEvaluation;

use Infection\Event\Events\MutationAnalysis\MutationEvaluation\MutationEvaluationWasStarted;
use Infection\Process\Runner\ProcessRunner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MutationEvaluationWasStarted::class)]
final class MutationEvaluationWasStartedTest extends TestCase
{
    public function test_it_exposes_the_mutation_count_and_process_runner(): void
    {
        $processRunner = $this->createStub(ProcessRunner::class);
        $event = new MutationEvaluationWasStarted(2, $processRunner);

        $this->assertSame(2, $event->mutationCount);
        $this->assertSame($processRunner, $event->processRunner);
    }
}
