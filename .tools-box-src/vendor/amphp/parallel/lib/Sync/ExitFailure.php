<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Sync;

final class ExitFailure implements ExitResult
{
    private $type;
    private $message;
    private $code;
    private $trace;
    private $previous;
    public function __construct(\Throwable $exception)
    {
        $this->type = \get_class($exception);
        $this->message = $exception->getMessage();
        $this->code = $exception->getCode();
        $this->trace = flattenThrowableBacktrace($exception);
        if ($previous = $exception->getPrevious()) {
            $this->previous = new self($previous);
        }
    }
    public function getResult()
    {
        throw $this->createException();
    }
    private function createException() : ContextPanicError
    {
        $previous = $this->previous ? $this->previous->createException() : null;
        return new ContextPanicError($this->type, $this->message, $this->code, $this->trace, $previous);
    }
}
