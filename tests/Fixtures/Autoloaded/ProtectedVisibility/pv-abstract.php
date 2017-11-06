<?php

namespace ProtectedVisibilityAbstract;

abstract class Test
{
    abstract protected function foo(int $param, $test = 1): bool;
}