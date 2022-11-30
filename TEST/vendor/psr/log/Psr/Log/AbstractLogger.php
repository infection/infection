<?php

namespace _HumbugBox9658796bb9f0\Psr\Log;

abstract class AbstractLogger implements LoggerInterface
{
    public function emergency($message, array $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }
    public function alert($message, array $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }
    public function critical($message, array $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }
    public function error($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }
    public function warning($message, array $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }
    public function notice($message, array $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }
    public function info($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }
    public function debug($message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}
