<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter;

interface OutputFormatterInterface
{
    public function setDecorated(bool $decorated);
    public function isDecorated();
    public function setStyle(string $name, OutputFormatterStyleInterface $style);
    public function hasStyle(string $name);
    public function getStyle(string $name);
    public function format(?string $message);
}
