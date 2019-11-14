<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration\Entry;

use Generator;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use PHPUnit\Framework\TestCase;

trait LogsAssertions
{
    use BadgeAssertions;

    private function assertLogsStateIs(
        Logs $logs,
        ?string $expectedTextLogFilePath,
        ?string $expectedSummaryLogFilePath,
        ?string $expectedDebugLogFilePath,
        ?string $expectedPerMutatorFilePath,
        ?Badge $expectedBadge
    ): void
    {
        $this->assertSame($expectedTextLogFilePath, $logs->getTextLogFilePath());
        $this->assertSame($expectedSummaryLogFilePath, $logs->getSummaryLogFilePath());
        $this->assertSame($expectedDebugLogFilePath, $logs->getDebugLogFilePath());
        $this->assertSame($expectedPerMutatorFilePath, $logs->getPerMutatorFilePath());

        $badge = $logs->getBadge();

        if (null === $expectedBadge) {
            $this->assertNull($badge);
        } else {
            $this->assertNotNull($badge);
            $this->assertBadgeStateIs($badge, $expectedBadge->getBranch());
        }
    }
}
