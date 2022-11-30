<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Completion;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\RuntimeException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\ArgvInput;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputDefinition;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputOption;
final class CompletionInput extends ArgvInput
{
    public const TYPE_ARGUMENT_VALUE = 'argument_value';
    public const TYPE_OPTION_VALUE = 'option_value';
    public const TYPE_OPTION_NAME = 'option_name';
    public const TYPE_NONE = 'none';
    private $tokens;
    private $currentIndex;
    private $completionType;
    private $completionName = null;
    private $completionValue = '';
    public static function fromString(string $inputStr, int $currentIndex) : self
    {
        \preg_match_all('/(?<=^|\\s)([\'"]?)(.+?)(?<!\\\\)\\1(?=$|\\s)/', $inputStr, $tokens);
        return self::fromTokens($tokens[0], $currentIndex);
    }
    public static function fromTokens(array $tokens, int $currentIndex) : self
    {
        $input = new self($tokens);
        $input->tokens = $tokens;
        $input->currentIndex = $currentIndex;
        return $input;
    }
    public function bind(InputDefinition $definition) : void
    {
        parent::bind($definition);
        $relevantToken = $this->getRelevantToken();
        if ('-' === $relevantToken[0]) {
            [$optionToken, $optionValue] = \explode('=', $relevantToken, 2) + ['', ''];
            $option = $this->getOptionFromToken($optionToken);
            if (null === $option && !$this->isCursorFree()) {
                $this->completionType = self::TYPE_OPTION_NAME;
                $this->completionValue = $relevantToken;
                return;
            }
            if (null !== $option && $option->acceptValue()) {
                $this->completionType = self::TYPE_OPTION_VALUE;
                $this->completionName = $option->getName();
                $this->completionValue = $optionValue ?: (!\str_starts_with($optionToken, '--') ? \substr($optionToken, 2) : '');
                return;
            }
        }
        $previousToken = $this->tokens[$this->currentIndex - 1];
        if ('-' === $previousToken[0] && '' !== \trim($previousToken, '-')) {
            $previousOption = $this->getOptionFromToken($previousToken);
            if (null !== $previousOption && $previousOption->acceptValue()) {
                $this->completionType = self::TYPE_OPTION_VALUE;
                $this->completionName = $previousOption->getName();
                $this->completionValue = $relevantToken;
                return;
            }
        }
        $this->completionType = self::TYPE_ARGUMENT_VALUE;
        foreach ($this->definition->getArguments() as $argumentName => $argument) {
            if (!isset($this->arguments[$argumentName])) {
                break;
            }
            $argumentValue = $this->arguments[$argumentName];
            $this->completionName = $argumentName;
            if (\is_array($argumentValue)) {
                $this->completionValue = $argumentValue ? $argumentValue[\array_key_last($argumentValue)] : null;
            } else {
                $this->completionValue = $argumentValue;
            }
        }
        if ($this->currentIndex >= \count($this->tokens)) {
            if (!isset($this->arguments[$argumentName]) || $this->definition->getArgument($argumentName)->isArray()) {
                $this->completionName = $argumentName;
                $this->completionValue = '';
            } else {
                $this->completionType = self::TYPE_NONE;
                $this->completionName = null;
                $this->completionValue = '';
            }
        }
    }
    public function getCompletionType() : string
    {
        return $this->completionType;
    }
    public function getCompletionName() : ?string
    {
        return $this->completionName;
    }
    public function getCompletionValue() : string
    {
        return $this->completionValue;
    }
    public function mustSuggestOptionValuesFor(string $optionName) : bool
    {
        return self::TYPE_OPTION_VALUE === $this->getCompletionType() && $optionName === $this->getCompletionName();
    }
    public function mustSuggestArgumentValuesFor(string $argumentName) : bool
    {
        return self::TYPE_ARGUMENT_VALUE === $this->getCompletionType() && $argumentName === $this->getCompletionName();
    }
    protected function parseToken(string $token, bool $parseOptions) : bool
    {
        try {
            return parent::parseToken($token, $parseOptions);
        } catch (RuntimeException $e) {
        }
        return $parseOptions;
    }
    private function getOptionFromToken(string $optionToken) : ?InputOption
    {
        $optionName = \ltrim($optionToken, '-');
        if (!$optionName) {
            return null;
        }
        if ('-' === ($optionToken[1] ?? ' ')) {
            return $this->definition->hasOption($optionName) ? $this->definition->getOption($optionName) : null;
        }
        return $this->definition->hasShortcut($optionName[0]) ? $this->definition->getOptionForShortcut($optionName[0]) : null;
    }
    private function getRelevantToken() : string
    {
        return $this->tokens[$this->isCursorFree() ? $this->currentIndex - 1 : $this->currentIndex];
    }
    private function isCursorFree() : bool
    {
        $nrOfTokens = \count($this->tokens);
        if ($this->currentIndex > $nrOfTokens) {
            throw new \LogicException('Current index is invalid, it must be the number of input tokens or one more.');
        }
        return $this->currentIndex >= $nrOfTokens;
    }
    public function __toString()
    {
        $str = '';
        foreach ($this->tokens as $i => $token) {
            $str .= $token;
            if ($this->currentIndex === $i) {
                $str .= '|';
            }
            $str .= ' ';
        }
        if ($this->currentIndex > $i) {
            $str .= '|';
        }
        return \rtrim($str);
    }
}
