<?php

namespace _HumbugBoxb47773b41c19\Amp\ParallelFunctions\Internal;

use _HumbugBoxb47773b41c19\Amp\Parallel\Worker\Environment;
use _HumbugBoxb47773b41c19\Amp\Parallel\Worker\Task;
class SerializedCallableTask implements Task
{
    private $function;
    private $args;
    public function __construct(string $function, array $args)
    {
        $this->function = $function;
        $this->args = $args;
    }
    public function run(Environment $environment)
    {
        $callable = \unserialize($this->function, ['allowed_classes' => \true]);
        if ($callable instanceof \__PHP_Incomplete_Class) {
            throw new \Error('When using a class instance as a callable, the class must be autoloadable');
        }
        if (\is_array($callable) && $callable[0] instanceof \__PHP_Incomplete_Class) {
            throw new \Error('When using a class instance method as a callable, the class must be autoloadable');
        }
        return $callable(...$this->args);
    }
}
