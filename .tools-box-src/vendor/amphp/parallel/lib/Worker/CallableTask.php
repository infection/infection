<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker;

final class CallableTask implements Task
{
    private $callable;
    private $args;
    public function __construct(callable $callable, array $args)
    {
        $this->callable = $callable;
        $this->args = $args;
    }
    public function run(Environment $environment)
    {
        if ($this->callable instanceof \__PHP_Incomplete_Class) {
            throw new \Error('When using a class instance as a callable, the class must be autoloadable');
        }
        if (\is_array($this->callable) && ($this->callable[0] ?? null) instanceof \__PHP_Incomplete_Class) {
            throw new \Error('When using a class instance method as a callable, the class must be autoloadable');
        }
        if (!\is_callable($this->callable)) {
            $message = 'User-defined functions must be autoloadable (that is, defined in a file autoloaded by composer)';
            if (\is_string($this->callable)) {
                $message .= \sprintf("; unable to load function '%s'", $this->callable);
            }
            throw new \Error($message);
        }
        return ($this->callable)(...$this->args);
    }
}
