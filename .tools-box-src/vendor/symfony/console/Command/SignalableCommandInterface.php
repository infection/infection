<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Command;

interface SignalableCommandInterface
{
    public function getSubscribedSignals() : array;
    public function handleSignal(int $signal) : void;
}
