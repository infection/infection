<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Infection\AbstractTestFramework\TestFrameworkAdapter;
use _HumbugBox9658796bb9f0\Infection\Event\InitialTestSuiteWasStarted;
use InvalidArgumentException;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class CiInitialTestsConsoleLoggerSubscriber implements EventSubscriber
{
    public function __construct(private OutputInterface $output, private TestFrameworkAdapter $testFrameworkAdapter)
    {
    }
    public function onInitialTestSuiteWasStarted(InitialTestSuiteWasStarted $event) : void
    {
        try {
            $version = $this->testFrameworkAdapter->getVersion();
        } catch (InvalidArgumentException) {
            $version = 'unknown';
        }
        $this->output->writeln(['', 'Running initial test suite...', '', sprintf('%s version: %s', $this->testFrameworkAdapter->getName(), $version)]);
    }
}
