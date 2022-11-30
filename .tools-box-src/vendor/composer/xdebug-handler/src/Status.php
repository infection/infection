<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Composer\XdebugHandler;

use _HumbugBoxb47773b41c19\Psr\Log\LoggerInterface;
use _HumbugBoxb47773b41c19\Psr\Log\LogLevel;
class Status
{
    const ENV_RESTART = 'XDEBUG_HANDLER_RESTART';
    const CHECK = 'Check';
    const ERROR = 'Error';
    const INFO = 'Info';
    const NORESTART = 'NoRestart';
    const RESTART = 'Restart';
    const RESTARTING = 'Restarting';
    const RESTARTED = 'Restarted';
    private $debug;
    private $envAllowXdebug;
    private $loaded;
    private $logger;
    private $modeOff;
    private $time;
    public function __construct(string $envAllowXdebug, bool $debug)
    {
        $start = \getenv(self::ENV_RESTART);
        Process::setEnv(self::ENV_RESTART);
        $this->time = \is_numeric($start) ? \round((\microtime(\true) - $start) * 1000) : 0;
        $this->envAllowXdebug = $envAllowXdebug;
        $this->debug = $debug && \defined('STDERR');
        $this->modeOff = \false;
    }
    public function setLogger(LoggerInterface $logger) : void
    {
        $this->logger = $logger;
    }
    public function report(string $op, ?string $data) : void
    {
        if ($this->logger !== null || $this->debug) {
            $callable = [$this, 'report' . $op];
            if (!\is_callable($callable)) {
                throw new \InvalidArgumentException('Unknown op handler: ' . $op);
            }
            $params = $data !== null ? [$data] : [];
            \call_user_func_array($callable, $params);
        }
    }
    private function output(string $text, ?string $level = null) : void
    {
        if ($this->logger !== null) {
            $this->logger->log($level !== null ? $level : LogLevel::DEBUG, $text);
        }
        if ($this->debug) {
            \fwrite(\STDERR, \sprintf('xdebug-handler[%d] %s', \getmypid(), $text . \PHP_EOL));
        }
    }
    private function reportCheck(string $loaded) : void
    {
        list($version, $mode) = \explode('|', $loaded);
        if ($version !== '') {
            $this->loaded = '(' . $version . ')' . ($mode !== '' ? ' xdebug.mode=' . $mode : '');
        }
        $this->modeOff = $mode === 'off';
        $this->output('Checking ' . $this->envAllowXdebug);
    }
    private function reportError(string $error) : void
    {
        $this->output(\sprintf('No restart (%s)', $error), LogLevel::WARNING);
    }
    private function reportInfo(string $info) : void
    {
        $this->output($info);
    }
    private function reportNoRestart() : void
    {
        $this->output($this->getLoadedMessage());
        if ($this->loaded !== null) {
            $text = \sprintf('No restart (%s)', $this->getEnvAllow());
            if (!(bool) \getenv($this->envAllowXdebug)) {
                $text .= ' Allowed by ' . ($this->modeOff ? 'xdebug.mode' : 'application');
            }
            $this->output($text);
        }
    }
    private function reportRestart() : void
    {
        $this->output($this->getLoadedMessage());
        Process::setEnv(self::ENV_RESTART, (string) \microtime(\true));
    }
    private function reportRestarted() : void
    {
        $loaded = $this->getLoadedMessage();
        $text = \sprintf('Restarted (%d ms). %s', $this->time, $loaded);
        $level = $this->loaded !== null ? LogLevel::WARNING : null;
        $this->output($text, $level);
    }
    private function reportRestarting(string $command) : void
    {
        $text = \sprintf('Process restarting (%s)', $this->getEnvAllow());
        $this->output($text);
        $text = 'Running ' . $command;
        $this->output($text);
    }
    private function getEnvAllow() : string
    {
        return $this->envAllowXdebug . '=' . \getenv($this->envAllowXdebug);
    }
    private function getLoadedMessage() : string
    {
        $loaded = $this->loaded !== null ? \sprintf('loaded %s', $this->loaded) : 'not loaded';
        return 'The Xdebug extension is ' . $loaded;
    }
}
