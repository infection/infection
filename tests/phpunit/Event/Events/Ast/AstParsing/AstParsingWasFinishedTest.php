<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Events\Ast\AstParsing;

use Infection\Event\Events\Ast\AstParsing\AstParsingWasFinished;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AstParsingWasFinished::class)]
final class AstParsingWasFinishedTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(AstParsingWasFinished::class, new AstParsingWasFinished());
    }
}
