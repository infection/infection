<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Command;

interface SignalableCommandInterface
{
    public function getSubscribedSignals() : array;
    public function handleSignal(int $signal) : void;
}
