<?php

namespace _HumbugBoxb47773b41c19\GetOpt;

trait WithOperands
{
    protected $operands = [];
    public function addOperands(array $operands)
    {
        foreach ($operands as $operand) {
            $this->addOperand($operand);
        }
        return $this;
    }
    public function addOperand(Operand $operand)
    {
        if ($operand->isRequired()) {
            foreach ($this->operands as $previousOperand) {
                $previousOperand->required();
            }
        }
        if ($this->hasOperands()) {
            $lastOperand = \array_slice($this->operands, -1)[0];
            if ($lastOperand->isMultiple()) {
                throw new \InvalidArgumentException(\sprintf('Operand %s is multiple - no more operands allowed', $lastOperand->getName()));
            }
        }
        $this->operands[] = $operand;
        return $this;
    }
    public function getOperands()
    {
        return $this->operands;
    }
    public function getOperand($index)
    {
        if (\is_string($index)) {
            $name = $index;
            foreach ($this->operands as $operand) {
                if ($operand->getName() === $name) {
                    return $operand;
                }
            }
            return null;
        }
        return isset($this->operands[$index]) ? $this->operands[$index] : null;
    }
    public function hasOperands()
    {
        return !empty($this->operands);
    }
}
