<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper;

use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputAwareInterface;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Input\InputInterface;
abstract class InputAwareHelper extends Helper implements InputAwareInterface
{
    protected $input;
    public function setInput(InputInterface $input)
    {
        $this->input = $input;
    }
}
