<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Infection\Event\MutableFileWasProcessed;
use _HumbugBox9658796bb9f0\Infection\Event\MutationGenerationWasFinished;
use _HumbugBox9658796bb9f0\Infection\Event\MutationGenerationWasStarted;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper\ProgressBar;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class MutationGeneratingConsoleLoggerSubscriber implements EventSubscriber
{
    private ProgressBar $progressBar;
    public function __construct(private OutputInterface $output)
    {
        $this->progressBar = new ProgressBar($this->output);
        $this->progressBar->setFormat('Processing source code files: %current%/%max%');
    }
    public function onMutationGenerationWasStarted(MutationGenerationWasStarted $event) : void
    {
        $this->output->writeln(['', '', 'Generate mutants...', '']);
        $this->progressBar->start($event->getMutableFilesCount());
    }
    public function onMutableFileWasProcessed(MutableFileWasProcessed $event) : void
    {
        $this->progressBar->advance();
    }
    public function onMutationGenerationWasFinished(MutationGenerationWasFinished $event) : void
    {
        $this->progressBar->finish();
    }
}
