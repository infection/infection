<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker\Internal;

use _HumbugBoxb47773b41c19\Amp\Parallel\Worker\Task;
final class Job
{
    private $id;
    private $task;
    public function __construct(Task $task)
    {
        static $id = 'a';
        $this->task = $task;
        $this->id = $id++;
    }
    public function getId() : string
    {
        return $this->id;
    }
    public function getTask() : Task
    {
        if ($this->task instanceof \__PHP_Incomplete_Class) {
            throw new \Error(\sprintf("Classes implementing %s must be autoloadable by the Composer autoloader", Task::class));
        }
        return $this->task;
    }
}
