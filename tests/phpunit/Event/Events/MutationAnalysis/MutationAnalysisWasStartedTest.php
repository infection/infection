<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Events\MutationAnalysis;

use Infection\Event\Events\MutationAnalysis\MutationAnalysisWasStarted;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MutationAnalysisWasStarted::class)]
final class MutationAnalysisWasStartedTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(MutationAnalysisWasStarted::class, new MutationAnalysisWasStarted());
    }
}
