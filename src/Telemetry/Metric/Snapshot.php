<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Infection\Telemetry\Metric;

use Infection\Telemetry\Metric\GarbageCollection\GarbageCollectorStatus;
use Infection\Telemetry\Metric\Memory\MemoryUsage;
use Infection\Telemetry\Metric\Time\HRTime;

final readonly class Snapshot
{
    public function __construct(
        public HRTime $time,
        public MemoryUsage $memoryUsage,
        public MemoryUsage $peakMemoryUsage,
        public GarbageCollectorStatus $garbageCollectorStatus,
    ) {
    }
}
