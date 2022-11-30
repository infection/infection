<?php

namespace _HumbugBox9658796bb9f0\Symfony\Component\Console\Helper;

interface HelperInterface
{
    public function setHelperSet(HelperSet $helperSet = null);
    public function getHelperSet();
    public function getName();
}
