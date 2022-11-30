<?php

namespace _HumbugBoxb47773b41c19\GetOpt;

class Command implements CommandInterface
{
    use WithOptions, WithOperands, WithMagicGetter;
    protected $name;
    protected $shortDescription;
    protected $longDescription;
    protected $handler;
    public function __construct($name, $handler, $options = null)
    {
        $this->setName($name);
        $this->handler = $handler;
        if ($options !== null) {
            $this->addOptions($options);
        }
    }
    public static function create($name, $handler, $options = null)
    {
        return new static($name, $handler, $options);
    }
    public function setName($name)
    {
        if (empty($name) || \preg_match('/(^| )-/', $name)) {
            throw new \InvalidArgumentException(\sprintf('Command name has to be an alphanumeric string not starting with dash, found \'%s\'', $name));
        }
        $this->name = $name;
        return $this;
    }
    public function setHandler($handler)
    {
        $this->handler = $handler;
        return $this;
    }
    public function setDescription($longDescription)
    {
        $this->longDescription = $longDescription;
        if ($this->shortDescription === null) {
            $this->shortDescription = $longDescription;
        }
        return $this;
    }
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
        if ($this->longDescription === null) {
            $this->longDescription = $shortDescription;
        }
        return $this;
    }
    public function getName()
    {
        return $this->name;
    }
    public function name()
    {
        return $this->name;
    }
    public function getHandler()
    {
        return $this->handler;
    }
    public function handler()
    {
        return $this->handler;
    }
    public function getDescription()
    {
        return $this->longDescription;
    }
    public function description()
    {
        return $this->longDescription;
    }
    public function getShortDescription()
    {
        return $this->shortDescription;
    }
    public function shortDescription()
    {
        return $this->shortDescription;
    }
}
