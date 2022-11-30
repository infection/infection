<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper;

use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputAwareInterface;
use _HumbugBox9658796bb9f0\Symfony\Component\Console\Input\InputInterface;
abstract class InputAwareHelper extends Helper implements InputAwareInterface
{
    protected $input;
    public function setInput(InputInterface $input)
    {
        $this->input = $input;
    }
}
