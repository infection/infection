<?php

namespace This_ReturnTypes;

class Foo
{
    public function bar()
    {
        $foo = 3;
        return $foo;
    }
    public function baz()
    {
        return true;
    }
    public function boo()
    {
        return $this->bar();
    }
    public function other()
    {
        return this();
    }
}
