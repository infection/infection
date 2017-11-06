<?php

namespace SameParent;

class SameParent
{
    public function foo() {}
}

class Child extends SameParent
{
    public function foo() {}
}