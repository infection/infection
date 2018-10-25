<?php

namespace ProtectedNonSameAbstract;

abstract class ProtectedNonSameAbstract
{
    protected abstract function foo();
}
class Child extends ProtectedNonSameAbstract
{
    protected function foo()
    {
    }
    private function bar()
    {
    }
}
