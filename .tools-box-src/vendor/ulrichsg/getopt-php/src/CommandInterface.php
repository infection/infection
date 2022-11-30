<?php

namespace _HumbugBoxb47773b41c19\GetOpt;

interface CommandInterface
{
    public function getName();
    public function getDescription();
    public function getShortDescription();
    public function getOperands();
    public function getOptions();
}
