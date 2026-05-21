<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Reporter;

use Closure;
use Infection\Event\Events\Reporting\ReportingWasFinished;
use Infection\Event\Events\Reporting\ReportingWasStarted;
use Infection\Reporter\EventDispatchingReporter;
use Infection\Reporter\Reporter;
use Infection\Tests\Fixtures\Event\EventDispatcherCollector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EventDispatchingReporter::class)]
final class EventDispatchingReporterTest extends TestCase
{
    public function test_it_dispatches_reporting_events_around_the_decorated_reporter(): void
    {
        $eventDispatcher = new EventDispatcherCollector();
        $decoratedReporter = new CallbackReporter(
            static function () use ($eventDispatcher): void {
                self::assertEquals(
                    [
                        new ReportingWasStarted(),
                    ],
                    $eventDispatcher->getEvents(),
                );
            },
        );

        $reporter = new EventDispatchingReporter(
            $decoratedReporter,
            $eventDispatcher,
        );

        $reporter->report();

        $this->assertEquals(
            [
                new ReportingWasStarted(),
                new ReportingWasFinished(),
            ],
            $eventDispatcher->getEvents(),
        );
    }
}

final readonly class CallbackReporter implements Reporter
{
    public function __construct(
        private Closure $callback,
    ) {
    }

    public function report(): void
    {
        ($this->callback)();
    }
}
