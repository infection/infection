<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration\Entry;

use Generator;
use Infection\Configuration\Entry\PhpUnit;
use PHPUnit\Framework\TestCase;

trait PhpUnitAssertions
{
    private function assertPhpUnitStateIs(
        PhpUnit $phpUnit,
        ?string $expectedConfigDir,
        ?string $expectedExecutablePath
    ): void
    {
        $this->assertSame($expectedConfigDir, $phpUnit->getConfigDir());
        $this->assertSame($expectedExecutablePath, $phpUnit->getCustomPath());
    }
}
