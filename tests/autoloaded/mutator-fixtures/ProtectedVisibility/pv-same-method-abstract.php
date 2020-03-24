<?php

namespace ProtectedSameAbstract;

abstract class SameAbstract
{
    protected abstract function foo();
}
class Child extends SameAbstract
{
    protected function foo()
    {
    }
    public function bar()
    {
    }
}
