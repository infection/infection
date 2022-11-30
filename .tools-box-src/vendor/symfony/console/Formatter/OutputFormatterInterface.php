<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Formatter;

interface OutputFormatterInterface
{
    public function setDecorated(bool $decorated);
    public function isDecorated() : bool;
    public function setStyle(string $name, OutputFormatterStyleInterface $style);
    public function hasStyle(string $name) : bool;
    public function getStyle(string $name) : OutputFormatterStyleInterface;
    public function format(?string $message) : ?string;
}
