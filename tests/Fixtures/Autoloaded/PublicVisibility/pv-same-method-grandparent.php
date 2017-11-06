<?php

namespace SameGrandParent;

class GrandParent
{
    public function foo() {}
}

class SameParent extends GrandParent
{
}

class Child extends SameParent
{
    public function foo() {}
}