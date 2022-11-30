<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Formatter;

interface WrappableOutputFormatterInterface extends OutputFormatterInterface
{
    public function formatAndWrap(?string $message, int $width);
}
