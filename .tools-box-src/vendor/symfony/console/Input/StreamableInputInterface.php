<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Input;

interface StreamableInputInterface extends InputInterface
{
    public function setStream($stream);
    public function getStream();
}
