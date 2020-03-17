<?php

namespace ProtectedParent;

abstract class SameAbstract
{
    protected abstract function foo();
}
class Child extends SameAbstract
{
    public function foo()
    {
    }
}
