<?php

namespace _HumbugBoxb47773b41c19\Amp\Parallel\Worker\Internal;

use _HumbugBoxb47773b41c19\Amp\Promise;
abstract class TaskResult
{
    private $id;
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    public function getId() : string
    {
        return $this->id;
    }
    public abstract function promise() : Promise;
}
