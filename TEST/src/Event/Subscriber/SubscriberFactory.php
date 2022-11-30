<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
interface SubscriberFactory
{
    public function create(OutputInterface $output) : EventSubscriber;
}
