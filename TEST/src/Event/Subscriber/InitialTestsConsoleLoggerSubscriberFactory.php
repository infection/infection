<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class InitialTestsConsoleLoggerSubscriberFactory implements SubscriberFactory
{
    public function __construct(private bool $skipProgressBar, private TestFrameworkAdapter $testFrameworkAdapter, private bool $debug)
    {
    }
    public function create(OutputInterface $output) : EventSubscriber
    {
        return $this->skipProgressBar ? new CiInitialTestsConsoleLoggerSubscriber($output, $this->testFrameworkAdapter) : new InitialTestsConsoleLoggerSubscriber($output, $this->testFrameworkAdapter, $this->debug);
    }
}
