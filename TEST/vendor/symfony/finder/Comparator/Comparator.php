<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Finder\Comparator;

class Comparator
{
    private $target;
    private $operator = '==';
    public function __construct(string $target = null, string $operator = '==')
    {
        if (null === $target) {
            trigger_deprecation('symfony/finder', '5.4', 'Constructing a "%s" without setting "$target" is deprecated.', __CLASS__);
        }
        $this->target = $target;
        $this->doSetOperator($operator);
    }
    public function getTarget()
    {
        if (null === $this->target) {
            trigger_deprecation('symfony/finder', '5.4', 'Calling "%s" without initializing the target is deprecated.', __METHOD__);
        }
        return $this->target;
    }
    public function setTarget(string $target)
    {
        trigger_deprecation('symfony/finder', '5.4', '"%s" is deprecated. Set the target via the constructor instead.', __METHOD__);
        $this->target = $target;
    }
    public function getOperator()
    {
        return $this->operator;
    }
    public function setOperator(string $operator)
    {
        trigger_deprecation('symfony/finder', '5.4', '"%s" is deprecated. Set the operator via the constructor instead.', __METHOD__);
        $this->doSetOperator('' === $operator ? '==' : $operator);
    }
    public function test($test)
    {
        if (null === $this->target) {
            trigger_deprecation('symfony/finder', '5.4', 'Calling "%s" without initializing the target is deprecated.', __METHOD__);
        }
        switch ($this->operator) {
            case '>':
                return $test > $this->target;
            case '>=':
                return $test >= $this->target;
            case '<':
                return $test < $this->target;
            case '<=':
                return $test <= $this->target;
            case '!=':
                return $test != $this->target;
        }
        return $test == $this->target;
    }
    private function doSetOperator(string $operator) : void
    {
        if (!\in_array($operator, ['>', '<', '>=', '<=', '==', '!='])) {
            throw new \InvalidArgumentException(\sprintf('Invalid operator "%s".', $operator));
        }
        $this->operator = $operator;
    }
}
