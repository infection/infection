<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Performance\Listener;

use Infection\EventDispatcher\EventDispatcher;
use Infection\Events\ApplicationExecutionFinished;
use Infection\Events\ApplicationExecutionStarted;
use Infection\Performance\Listener\PerformanceLoggerSubscriber;
use Infection\Performance\Memory\MemoryFormatter;
use Infection\Performance\Time\TimeFormatter;
use Infection\Performance\Time\Timer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class PerformanceLoggerSubscriberTest extends TestCase
{
    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $output;

    protected function setUp()
    {
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function test_it_reacts_on_application_execution_events()
    {
        $this->output->expects($this->once())
            ->method('writeln')
            ->with($this->callback(function ($parameter) {
                return \is_array($parameter) && '' === $parameter[0] && 0 === strpos($parameter[1], 'Time:');
            }));

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new PerformanceLoggerSubscriber(
            new Timer(),
            new TimeFormatter(),
            new MemoryFormatter(),
            $this->output
        ));

        $dispatcher->dispatch(new ApplicationExecutionStarted());
        $dispatcher->dispatch(new ApplicationExecutionFinished());
    }
}
