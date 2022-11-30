<?php

namespace _HumbugBoxb47773b41c19\GetOpt;

trait WithOptions
{
    protected $options = [];
    protected $optionMapping = [];
    public function addOptions($options)
    {
        if (\is_string($options)) {
            $options = OptionParser::parseString($options);
        }
        if (!\is_array($options)) {
            throw new \InvalidArgumentException('GetOpt(): argument must be string or array');
        }
        foreach ($options as $option) {
            $this->addOption($option);
        }
        return $this;
    }
    public function addOption($option)
    {
        if (!$option instanceof Option) {
            if (\is_string($option)) {
                $options = OptionParser::parseString($option);
                $option = $options[0];
            } elseif (\is_array($option)) {
                $option = OptionParser::parseArray($option);
            } else {
                throw new \InvalidArgumentException(\sprintf('$option has to be a string, an array or an Option. %s given', \gettype($option)));
            }
        }
        if ($this->conflicts($option)) {
            throw new \InvalidArgumentException('$option`s short and long name have to be unique');
        }
        $this->options[] = $option;
        $short = $option->getShort();
        $long = $option->getLong();
        if ($short) {
            $this->optionMapping[$short] = $option;
        }
        if ($long) {
            $this->optionMapping[$long] = $option;
        }
        return $this;
    }
    public function conflicts(Option $option)
    {
        $short = $option->getShort();
        $long = $option->getLong();
        return $short && isset($this->optionMapping[$short]) || $long && isset($this->optionMapping[$long]);
    }
    public function getOptions()
    {
        return $this->options;
    }
    public function getOption($name)
    {
        return isset($this->optionMapping[$name]) ? $this->optionMapping[$name] : null;
    }
    public function hasOptions()
    {
        return !empty($this->options);
    }
}
