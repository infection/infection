<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\Event\Subscriber;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Output\OutputInterface;
final class ChainSubscriberFactory
{
    private $factories;
    public function __construct(SubscriberFactory ...$factories)
    {
        $this->factories = $factories;
    }
    public function create(OutputInterface $output) : iterable
    {
        foreach ($this->factories as $factory) {
            (yield $factory->create($output));
        }
    }
}
