<?php

namespace _HumbugBoxb47773b41c19\GetOpt;

use _HumbugBoxb47773b41c19\GetOpt\ArgumentException\Invalid;
class Argument implements Describable
{
    use WithMagicGetter;
    const CLASSNAME = __CLASS__;
    const TRANSLATION_KEY = 'argument';
    protected $default;
    protected $validation;
    protected $name;
    protected $multiple;
    protected $value;
    protected $option;
    protected $validationMessage;
    public function __construct($default = null, callable $validation = null, $name = "arg")
    {
        if (!\is_null($default)) {
            $this->setDefaultValue($default);
        }
        if (!\is_null($validation)) {
            $this->setValidation($validation);
        }
        $this->name = $name;
    }
    public function setDefaultValue($value)
    {
        if (!\is_scalar($value)) {
            throw new \InvalidArgumentException("Default value must be scalar");
        }
        $this->default = $value;
        return $this;
    }
    public function setValidation(callable $callable, $message = null)
    {
        $this->validation = $callable;
        $this->validationMessage = $message;
        return $this;
    }
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    protected function getValidationMessage($value)
    {
        if (\is_callable($this->validationMessage)) {
            return \call_user_func($this->validationMessage, $this->option ?: $this, $value);
        }
        return \ucfirst(\sprintf($this->validationMessage ?: GetOpt::translate('value-invalid'), $this->describe(), $value));
    }
    public function isMultiple()
    {
        return $this->multiple;
    }
    public function multiple($multiple = \true)
    {
        $this->multiple = $multiple;
        return $this;
    }
    public function setOption(Option $option)
    {
        $this->option = $option;
        return $this;
    }
    public function setValue($value)
    {
        if ($this->validation && !$this->validates($value)) {
            throw new Invalid($this->getValidationMessage($value));
        }
        if ($this->isMultiple()) {
            $this->value = $this->value === null ? [$value] : \array_merge($this->value, [$value]);
        } else {
            $this->value = $value;
        }
        return $this;
    }
    public function getValue()
    {
        if ($this->value === null && $this->isMultiple()) {
            return [];
        }
        return $this->value;
    }
    public function validates($arg)
    {
        return (bool) \call_user_func($this->validation, $arg);
    }
    public function hasValidation()
    {
        return isset($this->validation);
    }
    public function hasDefaultValue()
    {
        return !\is_null($this->default);
    }
    public function getDefaultValue()
    {
        if ($this->isMultiple()) {
            return $this->default ? [$this->default] : [];
        }
        return $this->default;
    }
    public function getName()
    {
        return $this->name;
    }
    public function describe()
    {
        return $this->option ? $this->option->describe() : \sprintf('%s \'%s\'', GetOpt::translate(static::TRANSLATION_KEY), $this->getName());
    }
}
