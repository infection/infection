<?php

namespace SameParent;

class SameParent
{
    protected function foo()
    {
    }
}
class Child extends SameParent
{
    public function foo()
    {
    }
}