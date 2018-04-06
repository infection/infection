<?php

namespace NonSameAbstract;

abstract class NonSameAbstract
{
    public abstract function foo();
}
class Child extends NonSameAbstract
{
    public function foo()
    {
    }
    public function bar()
    {
    }
}
