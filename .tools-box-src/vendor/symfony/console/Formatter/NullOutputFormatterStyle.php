<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Formatter;

final class NullOutputFormatterStyle implements OutputFormatterStyleInterface
{
    public function apply(string $text) : string
    {
        return $text;
    }
    public function setBackground(string $color = null) : void
    {
    }
    public function setForeground(string $color = null) : void
    {
    }
    public function setOption(string $option) : void
    {
    }
    public function setOptions(array $options) : void
    {
    }
    public function unsetOption(string $option) : void
    {
    }
}
