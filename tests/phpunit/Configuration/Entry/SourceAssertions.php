<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration\Entry;

use Generator;
use Infection\Configuration\Entry\Source;
use PHPUnit\Framework\TestCase;

trait SourceAssertions
{
    private function assertSourceStateIs(
        Source $source,
        array $expectedDirectories,
        array $expectedExcludes
    ): void
    {
        $this->assertSame($expectedDirectories, $source->getDirectories());
        $this->assertSame($expectedExcludes, $source->getExcludes());
    }
}
