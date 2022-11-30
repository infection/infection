<?php

namespace _HumbugBoxb47773b41c19\GetOpt;

use _HumbugBoxb47773b41c19\GetOpt\ArgumentException\Invalid;
class Operand extends Argument
{
    const TRANSLATION_KEY = 'operand';
    const OPTIONAL = 0;
    const REQUIRED = 1;
    const MULTIPLE = 2;
    protected $required;
    protected $description;
    public function __construct($name, $mode = self::OPTIONAL)
    {
        $this->required = (bool) ($mode & self::REQUIRED);
        $this->multiple = (bool) ($mode & self::MULTIPLE);
        parent::__construct(null, null, $name);
    }
    public static function create($name, $mode = 0)
    {
        return new static($name, $mode);
    }
    public function isRequired()
    {
        return $this->required;
    }
    public function required($required = \true)
    {
        $this->required = $required;
        return $this;
    }
    public function setValue($value)
    {
        parent::setValue($value);
        return $this;
    }
    public function getValue()
    {
        $value = parent::getValue();
        return $value === null || $value === [] ? $this->getDefaultValue() : $value;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
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
}
