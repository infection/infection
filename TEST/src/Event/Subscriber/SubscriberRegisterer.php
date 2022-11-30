<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Infection\Event\EventDispatcher\EventDispatcher;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class SubscriberRegisterer
{
    public function __construct(private EventDispatcher $eventDispatcher, private ChainSubscriberFactory $subscriberRegistry)
    {
    }
    public function registerSubscribers(OutputInterface $output) : void
    {
        foreach ($this->subscriberRegistry->create($output) as $subscriber) {
            $this->eventDispatcher->addSubscriber($subscriber);
        }
    }
}
