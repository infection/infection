<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Process\Listener;

use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\MutantCreated;
use Infection\Events\MutantsCreatingFinished;
use Infection\Events\MutantsCreatingStarted;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
final class MutantCreatingConsoleLoggerSubscriber implements EventSubscriberInterface
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
        $this->progressBar->setFormat('Creating mutated files and processes: %current%/%max%');
    }

    public function getSubscribedEvents(): array
    {
        return [
            MutantsCreatingStarted::class => [$this, 'onMutantsCreatingStarted'],
            MutantCreated::class => [$this, 'onMutantCreated'],
            MutantsCreatingFinished::class => [$this, 'onMutantsCreatingFinished'],
        ];
    }

    public function onMutantsCreatingStarted(MutantsCreatingStarted $event): void
    {
        $this->output->writeln(['']);
        $this->progressBar->start($event->getMutantCount());
    }

    public function onMutantCreated(MutantCreated $event): void
    {
        $this->progressBar->advance();
    }

    public function onMutantsCreatingFinished(MutantsCreatingFinished $event): void
    {
        $this->progressBar->finish();
    }
}
