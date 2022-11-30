<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Question;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\InvalidArgumentException;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Exception\LogicException;
class Question
{
    private $question;
    private $attempts;
    private $hidden = \false;
    private $hiddenFallback = \true;
    private $autocompleterCallback;
    private $validator;
    private $default;
    private $normalizer;
    private $trimmable = \true;
    private $multiline = \false;
    public function __construct(string $question, $default = null)
    {
        $this->question = $question;
        $this->default = $default;
    }
    public function getQuestion()
    {
        return $this->question;
    }
    public function getDefault()
    {
        return $this->default;
    }
    public function isMultiline() : bool
    {
        return $this->multiline;
    }
    public function setMultiline(bool $multiline) : self
    {
        $this->multiline = $multiline;
        return $this;
    }
    public function isHidden()
    {
        return $this->hidden;
    }
    public function setHidden(bool $hidden)
    {
        if ($this->autocompleterCallback) {
            throw new LogicException('A hidden question cannot use the autocompleter.');
        }
        $this->hidden = $hidden;
        return $this;
    }
    public function isHiddenFallback()
    {
        return $this->hiddenFallback;
    }
    public function setHiddenFallback(bool $fallback)
    {
        $this->hiddenFallback = $fallback;
        return $this;
    }
    public function getAutocompleterValues()
    {
        $callback = $this->getAutocompleterCallback();
        return $callback ? $callback('') : null;
    }
    public function setAutocompleterValues(?iterable $values)
    {
        if (\is_array($values)) {
            $values = $this->isAssoc($values) ? \array_merge(\array_keys($values), \array_values($values)) : \array_values($values);
            $callback = static function () use($values) {
                return $values;
            };
        } elseif ($values instanceof \Traversable) {
            $valueCache = null;
            $callback = static function () use($values, &$valueCache) {
                return $valueCache ?? ($valueCache = \iterator_to_array($values, \false));
            };
        } else {
            $callback = null;
        }
        return $this->setAutocompleterCallback($callback);
    }
    public function getAutocompleterCallback() : ?callable
    {
        return $this->autocompleterCallback;
    }
    public function setAutocompleterCallback(callable $callback = null) : self
    {
        if ($this->hidden && null !== $callback) {
            throw new LogicException('A hidden question cannot use the autocompleter.');
        }
        $this->autocompleterCallback = $callback;
        return $this;
    }
    public function setValidator(callable $validator = null)
    {
        $this->validator = $validator;
        return $this;
    }
    public function getValidator()
    {
        return $this->validator;
    }
    public function setMaxAttempts(?int $attempts)
    {
        if (null !== $attempts && $attempts < 1) {
            throw new InvalidArgumentException('Maximum number of attempts must be a positive value.');
        }
        $this->attempts = $attempts;
        return $this;
    }
    public function getMaxAttempts()
    {
        return $this->attempts;
    }
    public function setNormalizer(callable $normalizer)
    {
        $this->normalizer = $normalizer;
        return $this;
    }
    public function getNormalizer()
    {
        return $this->normalizer;
    }
    protected function isAssoc(array $array)
    {
        return (bool) \count(\array_filter(\array_keys($array), 'is_string'));
    }
    public function isTrimmable() : bool
    {
        return $this->trimmable;
    }
    public function setTrimmable(bool $trimmable) : self
    {
        $this->trimmable = $trimmable;
        return $this;
    }
}
