<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Completion;

class Suggestion
{
    private string $value;
    public function __construct(string $value)
    {
        $this->value = $value;
    }
    public function getValue() : string
    {
        return $this->value;
    }
    public function __toString() : string
    {
        return $this->getValue();
    }
}
