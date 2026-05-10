<?php

declare(strict_types=1);

namespace Infection\Tests\Event\Events\Ast\AstEnrichment;

use Infection\Event\Events\Ast\AstEnrichment\AstEnrichmentWasFinished;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AstEnrichmentWasFinished::class)]
final class AstEnrichmentWasFinishedTest extends TestCase
{
    public function test_it_can_be_instantiated(): void
    {
        $this->assertInstanceOf(AstEnrichmentWasFinished::class, new AstEnrichmentWasFinished());
    }
}
