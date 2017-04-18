<?php

declare(strict_types=1);


namespace Infection\Process\Listener;


use Infection\EventDispatcher\EventSubscriberInterface;
use Infection\Events\MutationTestingFinished;
use Infection\Events\MutationTestingStarted;
use Infection\Events\MutantProcessFinished;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLoggerSubscriber implements EventSubscriberInterface
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
        $this->output = $output;
        $this->progressBar = $progressBar;
    }

    public function getSubscribedEvents()
    {
        return [
            MutationTestingStarted::class => [$this, 'onMutationTestingStarted'],
            MutationTestingFinished::class => [$this, 'onMutationTestingFinished'],
            MutantProcessFinished::class => [$this, 'onMutantProcessFinished'],
        ];
    }

    public function onMutationTestingStarted(MutationTestingStarted $event)
    {
        $this->progressBar->start($event->getMutationCount());
    }

    public function onMutationTestingFinished(MutationTestingFinished $event)
    {
        $this->progressBar->finish();
    }

    public function onMutantProcessFinished(MutantProcessFinished $event)
    {
        $this->progressBar->advance();
    }
}