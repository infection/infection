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
use Infection\Events\MutantsCreatingStarted;
use Infection\Events\MutantsCreatingFinished;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class MutantCreatingConsoleLoggerSubscriber implements EventSubscriberInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ProgressBar
     */
    private $progressBar;

    public function __construct(OutputInterface $output, ProgressBar $progressBar)
    {
        $this->progressBar = $progressBar;
        $this->output = $output;
    }

    public function getSubscribedEvents()
    {
        return [
            MutantsCreatingStarted::class => [$this, 'onMutantsCreatingStarted'],
            MutantCreated::class => [$this, 'onMutantCreated'],
            MutantsCreatingFinished::class => [$this, 'onMutantsCreatingFinished'],
        ];
    }

    public function onMutantsCreatingStarted(MutantsCreatingStarted $event)
    {
        $this->output->writeln(['']);
        $this->progressBar->start($event->getMutantCount());
    }

    public function onMutantCreated(MutantCreated $event)
    {
        $this->progressBar->advance();
    }

    public function onMutantsCreatingFinished(MutantsCreatingFinished $event)
    {
        $this->progressBar->finish();
    }
}
