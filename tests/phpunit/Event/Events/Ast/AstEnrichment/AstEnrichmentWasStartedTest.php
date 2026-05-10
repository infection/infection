<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Events\Ast\AstEnrichment;

use Infection\Event\Events\Ast\AstEnrichment\AstEnrichmentWasStarted;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AstEnrichmentWasStarted::class)]
final class AstEnrichmentWasStartedTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(AstEnrichmentWasStarted::class, new AstEnrichmentWasStarted());
    }
}
