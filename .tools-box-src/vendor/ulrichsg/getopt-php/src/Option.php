<?php

namespace _HumbugBoxb47773b41c19\GetOpt;

use _HumbugBoxb47773b41c19\GetOpt\ArgumentException\Invalid;
use _HumbugBoxb47773b41c19\GetOpt\ArgumentException\Missing;
class Option implements Describable
{
    use WithMagicGetter;
    const CLASSNAME = __CLASS__;
    private $short;
    private $long;
    private $mode;
    private $description = '';
    private $argument;
    private $value = null;
    public function __construct($short, $long = null, $mode = GetOpt::NO_ARGUMENT)
    {
        if (!$short && !$long) {
            throw new \InvalidArgumentException("The short and long name may not both be empty");
        }
        if ($short == $long) {
            throw new \InvalidArgumentException("The short and long names have to be unique");
        }
        $this->setShort($short);
        $this->setLong($long);
        $this->setMode($mode);
        $this->argument = new Argument();
        $this->argument->multiple($this->mode === GetOpt::MULTIPLE_ARGUMENT);
        $this->argument->setOption($this);
    }
    public static function create($short, $long = null, $mode = GetOpt::NO_ARGUMENT)
    {
        return new static($short, $long, $mode);
    }
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function description()
    {
        return $this->description;
    }
    public function setDefaultValue($value)
    {
        $this->argument->setDefaultValue($value);
        return $this;
    }
    public function setValidation($function, $message = null)
    {
        $this->argument->setValidation($function, $message);
        return $this;
    }
    public function setArgumentName($name)
    {
        $this->argument->setName($name);
        return $this;
    }
    public function setArgument(Argument $arg)
    {
        if ($this->mode == GetOpt::NO_ARGUMENT) {
            throw new \InvalidArgumentException("Option should not have any argument");
        }
        $this->argument = clone $arg;
        $this->argument->multiple($this->mode === GetOpt::MULTIPLE_ARGUMENT);
        $this->argument->setOption($this);
        return $this;
    }
    public function setShort($short)
    {
        if (!(\is_null($short) || \preg_match("/^[a-zA-Z0-9?!§\$%#]\$/", $short))) {
            throw new \InvalidArgumentException(\sprintf('Short option must be null or one of [a-zA-Z0-9?!§$%%#], found \'%s\'', $short));
        }
        $this->short = $short;
        return $this;
    }
    public function getShort()
    {
        return $this->short;
    }
    public function getName()
    {
        return $this->getLong() ?: $this->getShort();
    }
    public function short()
    {
        return $this->short;
    }
    public function setLong($long)
    {
        if (!(\is_null($long) || \preg_match("/^[a-zA-Z0-9][a-zA-Z0-9_-]*\$/", $long))) {
            throw new \InvalidArgumentException(\sprintf('Long option must be null or an alphanumeric string, found \'%s\'', $long));
        }
        $this->long = $long;
        return $this;
    }
    public function getLong()
    {
        return $this->long;
    }
    public function long()
    {
        return $this->long;
    }
    public function setMode($mode)
    {
        if (!\in_array($mode, [GetOpt::NO_ARGUMENT, GetOpt::OPTIONAL_ARGUMENT, GetOpt::REQUIRED_ARGUMENT, GetOpt::MULTIPLE_ARGUMENT], \true)) {
            throw new \InvalidArgumentException(\sprintf('Option mode must be one of %s, %s, %s and %s', 'GetOpt::NO_ARGUMENT', 'GetOpt::OPTIONAL_ARGUMENT', 'GetOpt::REQUIRED_ARGUMENT', 'GetOpt::MULTIPLE_ARGUMENT'));
        }
        $this->mode = $mode;
        return $this;
    }
    public function getMode()
    {
        return $this->mode;
    }
    public function mode()
    {
        return $this->mode;
    }
    public function getArgument()
    {
        return $this->argument;
    }
    public function setValue($value = null)
    {
        if ($value === null) {
            if (\in_array($this->mode, [GetOpt::REQUIRED_ARGUMENT, GetOpt::MULTIPLE_ARGUMENT])) {
                throw new Missing(\sprintf(GetOpt::translate('option-argument-missing'), $this->getName()));
            }
            $value = $this->argument->getValue() + 1;
        }
        $this->argument->setValue($value);
        return $this;
    }
    public function getValue()
    {
        $value = $this->argument->getValue();
        return $value === null || $value === [] ? $this->argument->getDefaultValue() : $value;
    }
    public function value()
    {
        return $this->getValue();
    }
    public function __toString()
    {
        $value = $this->getValue();
        return !\is_array($value) ? (string) $value : \implode(',', $value);
    }
    public function describe()
    {
        return \sprintf('%s \'%s\'', GetOpt::translate('option'), $this->getName());
    }
}
