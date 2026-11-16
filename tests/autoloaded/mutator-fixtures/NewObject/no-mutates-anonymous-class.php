<?php

namespace NewObject_MutatesAnonymousClass;

class Test
{
    public function test()
    {
        return new class
        {
            private $foo;
            public function getFoo()
            {
                return $this->foo;
            }
        };
    }
}
