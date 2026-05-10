<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Events\Ast;

use Infection\Event\Events\Ast\AstProcessingWasFinished;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AstProcessingWasFinished::class)]
final class AstProcessingWasFinishedTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(AstProcessingWasFinished::class, new AstProcessingWasFinished());
    }
}
