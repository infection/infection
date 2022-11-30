<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Formatter;

interface OutputFormatterStyleInterface
{
    public function setForeground(string $color = null);
    public function setBackground(string $color = null);
    public function setOption(string $option);
    public function unsetOption(string $option);
    public function setOptions(array $options);
    public function apply(string $text) : string;
}
