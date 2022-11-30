<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Infection\Event\MutationGenerationWasStarted;
use function _HumbugBox9658796bb9f0\Safe\sprintf;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class CiMutationGeneratingConsoleLoggerSubscriber implements EventSubscriber
{
    public function __construct(private OutputInterface $output)
    {
    }
    public function onMutationGenerationWasStarted(MutationGenerationWasStarted $event) : void
    {
        $this->output->writeln(['', 'Generate mutants...', '', sprintf('Processing source code files: %s', $event->getMutableFilesCount())]);
    }
}
