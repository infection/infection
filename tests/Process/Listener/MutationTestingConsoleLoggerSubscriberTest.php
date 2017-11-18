<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Process\Listener;

use Infection\Console\OutputFormatter\OutputFormatter;
use Infection\Differ\DiffColorizer;
use Infection\EventDispatcher\EventDispatcher;
use Infection\Events\MutantProcessFinished;
use Infection\Events\MutationTestingFinished;
use Infection\Events\MutationTestingStarted;
use Infection\Mutant\MetricsCalculator;
use Infection\Process\Listener\MutationTestingConsoleLoggerSubscriber;
use Infection\Process\MutantProcess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class MutationTestingConsoleLoggerSubscriberTest extends TestCase
{
    /**
     * @var OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $output;

    /**
     * @var OutputFormatter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $outputFormatter;

    /**
     * @var MetricsCalculator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metricsCalculator;

    /**
     * @var DiffColorizer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $diffColorizer;

    protected function setUp()
    {
        $this->output = $this->createMock(OutputInterface::class);
        $this->outputFormatter = $this->createMock(OutputFormatter::class);
        $this->metricsCalculator = $this->getMockBuilder(MetricsCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->diffColorizer = $this->createMock(DiffColorizer::class);
    }

    public function test_it_reacts_on_mutation_testing_started()
    {
        $this->outputFormatter
            ->expects($this->once())
            ->method('start');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->diffColorizer,
            false
        ));

        $dispatcher->dispatch(new MutationTestingStarted(1));
    }

    public function test_it_reacts_on_mutation_process_finished()
    {
        $this->metricsCalculator
            ->expects($this->once())
            ->method('collect');

        $this->outputFormatter
            ->expects($this->once())
            ->method('advance');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->diffColorizer,
            false
        ));

        $dispatcher->dispatch(new MutantProcessFinished($this->createMock(MutantProcess::class)));
    }

    public function test_it_reacts_on_mutation_testing_finished()
    {
        $this->outputFormatter
            ->expects($this->once())
            ->method('finish');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new MutationTestingConsoleLoggerSubscriber(
            $this->output,
            $this->outputFormatter,
            $this->metricsCalculator,
            $this->diffColorizer,
            false
        ));

        $dispatcher->dispatch(new MutationTestingFinished());
    }
}
