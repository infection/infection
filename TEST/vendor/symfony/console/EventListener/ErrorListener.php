<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\EventListener;

use _HumbugBox9658796bb9f0\Psr\Log\LoggerInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\ConsoleEvents;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Event\ConsoleErrorEvent;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Event\ConsoleEvent;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Event\ConsoleTerminateEvent;
use _HumbugBox9658796bb9f0\Symfony\Component\EventDispatcher\EventSubscriberInterface;
class ErrorListener implements EventSubscriberInterface
{
    private $logger;
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }
    public function onConsoleError(ConsoleErrorEvent $event)
    {
        if (null === $this->logger) {
            return;
        }
        $error = $event->getError();
        if (!($inputString = $this->getInputString($event))) {
            $this->logger->critical('An error occurred while using the console. Message: "{message}"', ['exception' => $error, 'message' => $error->getMessage()]);
            return;
        }
        $this->logger->critical('Error thrown while running command "{command}". Message: "{message}"', ['exception' => $error, 'command' => $inputString, 'message' => $error->getMessage()]);
    }
    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        if (null === $this->logger) {
            return;
        }
        $exitCode = $event->getExitCode();
        if (0 === $exitCode) {
            return;
        }
        if (!($inputString = $this->getInputString($event))) {
            $this->logger->debug('The console exited with code "{code}"', ['code' => $exitCode]);
            return;
        }
        $this->logger->debug('Command "{command}" exited with code "{code}"', ['command' => $inputString, 'code' => $exitCode]);
    }
    public static function getSubscribedEvents()
    {
        return [ConsoleEvents::ERROR => ['onConsoleError', -128], ConsoleEvents::TERMINATE => ['onConsoleTerminate', -128]];
    }
    private static function getInputString(ConsoleEvent $event) : ?string
    {
        $commandName = $event->getCommand() ? $event->getCommand()->getName() : null;
        $input = $event->getInput();
        if (\method_exists($input, '__toString')) {
            if ($commandName) {
                return \str_replace(["'{$commandName}'", "\"{$commandName}\""], $commandName, (string) $input);
            }
            return (string) $input;
        }
        return $commandName;
    }
}
