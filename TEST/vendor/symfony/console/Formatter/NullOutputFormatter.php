<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter;

final class NullOutputFormatter implements OutputFormatterInterface
{
    private $style;
    public function format(?string $message) : ?string
    {
        return null;
    }
    public function getStyle(string $name) : OutputFormatterStyleInterface
    {
        return $this->style ?? ($this->style = new NullOutputFormatterStyle());
    }
    public function hasStyle(string $name) : bool
    {
        return \false;
    }
    public function isDecorated() : bool
    {
        return \false;
    }
    public function setDecorated(bool $decorated) : void
    {
    }
    public function setStyle(string $name, OutputFormatterStyleInterface $style) : void
    {
    }
}
