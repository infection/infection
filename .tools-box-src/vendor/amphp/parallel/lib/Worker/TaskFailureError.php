<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

use function _HumbugBoxb47773b41c19\Amp\Parallel\Sync\formatFlattenedBacktrace;
final class TaskFailureError extends TaskError implements TaskFailureThrowable
{
    private $originalMessage;
    private $originalCode;
    private $originalTrace;
    public function __construct(string $className, string $message, $code, array $trace, ?TaskFailureThrowable $previous = null)
    {
        $format = 'Uncaught %s in worker with message "%s" and code "%s"; use %s::getOriginalTrace() ' . 'for the stack trace in the worker';
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
        return $this->getWorkerTrace();
    }
}
