<?php

namespace HumbugBox420\KevinGH\RequirementChecker;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;
use function count;
use function get_cfg_var;
final class RequirementCollection implements IteratorAggregate, Countable
{
    private $requirements = [];
    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->requirements);
    }
    public function count() : int
    {
        return count($this->requirements);
    }
    public function add(Requirement $requirement) : void
    {
        $this->requirements[] = $requirement;
    }
    public function addRequirement(IsFulfilled $checkIsFulfilled, string $testMessage, string $helpText) : void
    {
        $this->add(new Requirement($checkIsFulfilled, $testMessage, $helpText));
    }
    public function getRequirements() : array
    {
        return $this->requirements;
    }
    public function getPhpIniPath()
    {
        return get_cfg_var('cfg_file_path');
    }
    public function evaluateRequirements()
    {
        return \array_reduce($this->requirements, function (bool $checkPassed, Requirement $requirement) : bool {
            return $checkPassed && $requirement->isFulfilled();
        }, \true);
    }
}
