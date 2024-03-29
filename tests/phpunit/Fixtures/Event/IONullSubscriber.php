<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Event;

use Infection\Event\Subscriber\EventSubscriber;
use Symfony\Component\Console\Output\OutputInterface;

final class IONullSubscriber implements EventSubscriber
{
    public function __construct(private readonly OutputInterface $output)
    {
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
}
