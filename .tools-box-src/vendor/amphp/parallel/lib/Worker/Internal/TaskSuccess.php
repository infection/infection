<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker\Internal;

use _HumbugBoxb47773b41c19\Amp\Failure;
use _HumbugBoxb47773b41c19\Amp\Parallel\Worker\Task;
use _HumbugBoxb47773b41c19\Amp\Promise;
use _HumbugBoxb47773b41c19\Amp\Success;
final class TaskSuccess extends TaskResult
{
    private $result;
    public function __construct(string $id, $result)
    {
        parent::__construct($id);
        $this->result = $result;
    }
    public function promise() : Promise
    {
        if ($this->result instanceof \__PHP_Incomplete_Class) {
            return new Failure(new \Error(\sprintf("Class instances returned from %s::run() must be autoloadable by the Composer autoloader", Task::class)));
        }
        return new Success($this->result);
    }
}
