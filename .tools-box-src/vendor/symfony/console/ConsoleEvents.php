<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Event\ConsoleCommandEvent;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Event\ConsoleErrorEvent;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Event\ConsoleSignalEvent;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Event\ConsoleTerminateEvent;
final class ConsoleEvents
{
    /**
    @Event("Symfony\Component\Console\Event\ConsoleCommandEvent")
    */
    public const COMMAND = 'console.command';
    /**
    @Event("Symfony\Component\Console\Event\ConsoleSignalEvent")
    */
    public const SIGNAL = 'console.signal';
    /**
    @Event("Symfony\Component\Console\Event\ConsoleTerminateEvent")
    */
    public const TERMINATE = 'console.terminate';
    /**
    @Event("Symfony\Component\Console\Event\ConsoleErrorEvent")
    */
    public const ERROR = 'console.error';
    public const ALIASES = [ConsoleCommandEvent::class => self::COMMAND, ConsoleErrorEvent::class => self::ERROR, ConsoleSignalEvent::class => self::SIGNAL, ConsoleTerminateEvent::class => self::TERMINATE];
}
