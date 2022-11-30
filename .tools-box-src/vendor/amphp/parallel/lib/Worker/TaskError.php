<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

class TaskError extends \Error
{
    private $name;
    private $trace;
    public function __construct(string $name, string $message = '', string $trace = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->name = $name;
        $this->trace = $trace;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getWorkerTrace() : string
    {
        return $this->trace;
    }
}
