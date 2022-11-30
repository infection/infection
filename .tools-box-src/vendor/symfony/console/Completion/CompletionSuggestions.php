<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Completion;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputOption;
final class CompletionSuggestions
{
    private $valueSuggestions = [];
    private $optionSuggestions = [];
    public function suggestValue(string|Suggestion $value) : static
    {
        $this->valueSuggestions[] = !$value instanceof Suggestion ? new Suggestion($value) : $value;
        return $this;
    }
    public function suggestValues(array $values) : static
    {
        foreach ($values as $value) {
            $this->suggestValue($value);
        }
        return $this;
    }
    public function suggestOption(InputOption $option) : static
    {
        $this->optionSuggestions[] = $option;
        return $this;
    }
    public function suggestOptions(array $options) : static
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
