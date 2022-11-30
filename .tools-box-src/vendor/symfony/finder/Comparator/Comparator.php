<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Finder\Comparator;

class Comparator
{
    private string $target;
    private string $operator;
    public function __construct(string $target, string $operator = '==')
    {
        if (!\in_array($operator, ['>', '<', '>=', '<=', '==', '!='])) {
            throw new \InvalidArgumentException(\sprintf('Invalid operator "%s".', $operator));
        }
        $this->target = $target;
        $this->operator = $operator;
    }
    public function getTarget() : string
    {
        return $this->target;
    }
    public function getOperator() : string
    {
        return $this->operator;
    }
    public function test(mixed $test) : bool
    {
        return match ($this->operator) {
            '>' => $test > $this->target,
            '>=' => $test >= $this->target,
            '<' => $test < $this->target,
            '<=' => $test <= $this->target,
            '!=' => $test != $this->target,
            default => $test == $this->target,
        };
    }
}
