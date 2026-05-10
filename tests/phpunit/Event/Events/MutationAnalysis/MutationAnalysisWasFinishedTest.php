<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Events\MutationAnalysis;

use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasFinished;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MutationAnalysisWasFinished::class)]
final class MutationAnalysisWasFinishedTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(MutationAnalysisWasFinished::class, new MutationAnalysisWasFinished());
    }
}
