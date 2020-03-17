<?php

namespace SameAbstract;

abstract class SameAbstract
{
    public abstract function foo();
}
class Child extends SameAbstract
{
    public function foo()
    {
    }
    private function bar()
    {
    }
}
