<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Question;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Exception\InvalidArgumentException;
class ChoiceQuestion extends Question
{
    private array $choices;
    private bool $multiselect = \false;
    private string $prompt = ' > ';
    private string $errorMessage = 'Value "%s" is invalid';
    public function __construct(string $question, array $choices, mixed $default = null)
    {
        if (!$choices) {
            throw new \LogicException('Choice question must have at least 1 choice available.');
        }
        parent::__construct($question, $default);
        $this->choices = $choices;
        $this->setValidator($this->getDefaultValidator());
        $this->setAutocompleterValues($choices);
    }
    public function getChoices() : array
    {
        return $this->choices;
    }
    public function setMultiselect(bool $multiselect) : static
    {
        $this->multiselect = $multiselect;
        $this->setValidator($this->getDefaultValidator());
        return $this;
    }
    public function isMultiselect() : bool
    {
        return $this->multiselect;
    }
    public function getPrompt() : string
    {
        return $this->prompt;
    }
    public function setPrompt(string $prompt) : static
    {
        $this->prompt = $prompt;
        return $this;
    }
    public function setErrorMessage(string $errorMessage) : static
    {
        $this->errorMessage = $errorMessage;
        $this->setValidator($this->getDefaultValidator());
        return $this;
    }
    private function getDefaultValidator() : callable
    {
        $choices = $this->choices;
        $errorMessage = $this->errorMessage;
        $multiselect = $this->multiselect;
        $isAssoc = $this->isAssoc($choices);
        return function ($selected) use($choices, $errorMessage, $multiselect, $isAssoc) {
            if ($multiselect) {
                if (!\preg_match('/^[^,]+(?:,[^,]+)*$/', (string) $selected, $matches)) {
                    throw new InvalidArgumentException(\sprintf($errorMessage, $selected));
                }
                $selectedChoices = \explode(',', (string) $selected);
            } else {
                $selectedChoices = [$selected];
            }
            if ($this->isTrimmable()) {
                foreach ($selectedChoices as $k => $v) {
                    $selectedChoices[$k] = \trim((string) $v);
                }
            }
            $multiselectChoices = [];
            foreach ($selectedChoices as $value) {
                $results = [];
                foreach ($choices as $key => $choice) {
                    if ($choice === $value) {
                        $results[] = $key;
                    }
                }
                if (\count($results) > 1) {
                    throw new InvalidArgumentException(\sprintf('The provided answer is ambiguous. Value should be one of "%s".', \implode('" or "', $results)));
                }
                $result = \array_search($value, $choices);
                if (!$isAssoc) {
                    if (\false !== $result) {
                        $result = $choices[$result];
                    } elseif (isset($choices[$value])) {
                        $result = $choices[$value];
                    }
                } elseif (\false === $result && isset($choices[$value])) {
                    $result = $value;
                }
                if (\false === $result) {
                    throw new InvalidArgumentException(\sprintf($errorMessage, $value));
                }
                $multiselectChoices[] = $isAssoc ? (string) $result : $result;
            }
            if ($multiselect) {
                return $multiselectChoices;
            }
            return \current($multiselectChoices);
        };
    }
}
