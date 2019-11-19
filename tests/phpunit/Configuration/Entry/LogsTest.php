<?php

declare(strict_types=1);

namespace Infection\Tests\Configuration\Entry;

use Generator;
use Infection\Configuration\Entry\Badge;
use Infection\Configuration\Entry\Logs;
use PHPUnit\Framework\TestCase;

final class LogsTest extends TestCase
{
    use LogsAssertions;

    /**
     * @dataProvider valuesProvider
     */
    public function test_it_can_be_instantiated(
        ?string $textLogFilePath,
        ?string $summaryLogFilePath,
        ?string $debugLogFilePath,
        ?string $perMutatorFilePath,
        ?Badge $badge
    ): void
    {
        $logs = new Logs(
            $textLogFilePath,
            $summaryLogFilePath,
            $debugLogFilePath,
            $perMutatorFilePath,
            $badge
        );

        $this->assertLogsStateIs(
            $logs,
            $textLogFilePath,
            $summaryLogFilePath,
            $debugLogFilePath,
            $perMutatorFilePath,
            $badge
        );
    }

    public function valuesProvider(): Generator
    {
        yield 'minimal' => [
            null,
            null,
            null,
            null,
            null,
        ];

        yield 'complete' => [
            'text.log',
            'summary.log',
            'debug.log',
            'perMutator.log',
            new Badge('master'),
        ];
    }
}
