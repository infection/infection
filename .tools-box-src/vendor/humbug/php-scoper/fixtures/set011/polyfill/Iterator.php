<?php

namespace _HumbugBoxb47773b41c19;

interface Iterator extends \Traversable
{
    public function current();
    public function next() : void;
    public function key();
    public function valid() : bool;
    public function rewind() : void;
}
\class_alias('_HumbugBoxb47773b41c19\\Iterator', 'Iterator', \false);
