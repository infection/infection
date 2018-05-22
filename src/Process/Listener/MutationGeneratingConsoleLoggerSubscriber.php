<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process\Listener;

use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\MutableFileProcessed;
use Infection\Events\MutationGeneratingFinished;
use Infection\Events\MutationGeneratingStarted;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class MutationGeneratingConsoleLoggerSubscriber implements EventSubscriberInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;

        $this->progressBar = new ProgressBar($this->output);
        $this->progressBar->setFormat('Processing source code files: %current%/%max%');
    }

    public function getSubscribedEvents(): array
    {
        return [
            MutationGeneratingStarted::class => [$this, 'onMutationGeneratingStarted'],
            MutableFileProcessed::class => [$this, 'onMutableFileProcessed'],
            MutationGeneratingFinished::class => [$this, 'onMutationGeneratingFinished'],
        ];
    }

    public function onMutationGeneratingStarted(MutationGeneratingStarted $event)
    {
        $this->output->writeln(['', '', 'Generate mutants...', '']);
        $this->progressBar->start($event->getMutableFilesCount());
    }

    public function onMutableFileProcessed(MutableFileProcessed $event)
    {
        $this->progressBar->advance();
    }

    public function onMutationGeneratingFinished(MutationGeneratingFinished $event)
    {
        $this->progressBar->finish();
    }
}
