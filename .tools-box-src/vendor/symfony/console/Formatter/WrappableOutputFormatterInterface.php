<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Formatter;

interface WrappableOutputFormatterInterface extends OutputFormatterInterface
{
    public function formatAndWrap(?string $message, int $width);
}
