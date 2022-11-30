<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Sync;

final class ContextPanicError extends PanicError
{
    private $originalMessage;
    private $originalCode;
    private $originalTrace;
    public function __construct(string $className, string $message, $code, array $trace, ?self $previous = null)
    {
        $format = 'Uncaught %s in child process or thread with message "%s" and code "%s"; use %s::getOriginalTrace() ' . 'for the stack trace in the child process or thread';
        parent::__construct($className, \sprintf($format, $className, $message, $code, self::class), formatFlattenedBacktrace($trace), $previous);
        $this->originalMessage = $message;
        $this->originalCode = $code;
        $this->originalTrace = $trace;
    }
    public function getOriginalClassName() : string
    {
        return $this->getName();
    }
    public function getOriginalMessage() : string
    {
        return $this->originalMessage;
    }
    public function getOriginalCode()
    {
        return $this->originalCode;
    }
    public function getOriginalTrace() : array
    {
        return $this->originalTrace;
    }
    public function getOriginalTraceAsString() : string
    {
        return $this->getPanicTrace();
    }
}
