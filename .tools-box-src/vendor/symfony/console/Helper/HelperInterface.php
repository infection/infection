<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper;

interface HelperInterface
{
    public function setHelperSet(HelperSet $helperSet = null);
    public function getHelperSet() : ?HelperSet;
    public function getName();
}
