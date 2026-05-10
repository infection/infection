<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Events\Ast;

use Infection\Event\Events\Ast\AstProcessingWasStarted;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AstProcessingWasStarted::class)]
final class AstProcessingWasStartedTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(AstProcessingWasStarted::class, new AstProcessingWasStarted());
    }
}
