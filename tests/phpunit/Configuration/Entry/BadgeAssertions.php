<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration\Entry;

use Infection\Configuration\Entry\Badge;

trait BadgeAssertions
{
    private function assertBadgeStateIs(
        Badge $badge,
        string $expectedBranch
    ): void
    {
        $this->assertSame($expectedBranch, $badge->getBranch());
    }
}
