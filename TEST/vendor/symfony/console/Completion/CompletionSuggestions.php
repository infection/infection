<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Completion;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputOption;
final class CompletionSuggestions
{
    private $valueSuggestions = [];
    private $optionSuggestions = [];
    public function suggestValue($value) : self
    {
        $this->valueSuggestions[] = !$value instanceof Suggestion ? new Suggestion($value) : $value;
        return $this;
    }
    public function suggestValues(array $values) : self
    {
        foreach ($values as $value) {
            $this->suggestValue($value);
        }
        return $this;
    }
    public function suggestOption(InputOption $option) : self
    {
        $this->optionSuggestions[] = $option;
        return $this;
    }
    public function suggestOptions(array $options) : self
    {
        foreach ($options as $option) {
            $this->suggestOption($option);
        }
        return $this;
    }
    public function getOptionSuggestions() : array
    {
        return $this->optionSuggestions;
    }
    public function getValueSuggestions() : array
    {
        return $this->valueSuggestions;
    }
}
