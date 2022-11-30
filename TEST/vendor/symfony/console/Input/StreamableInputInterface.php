<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Input;

interface StreamableInputInterface extends InputInterface
{
    public function setStream($stream);
    public function getStream();
}
