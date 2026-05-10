<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Events\Ast\AstParsing;

use Infection\Event\Events\Ast\AstParsing\AstParsingWasStarted;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AstParsingWasStarted::class)]
final class AstParsingWasStartedTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(AstParsingWasStarted::class, new AstParsingWasStarted());
    }
}
