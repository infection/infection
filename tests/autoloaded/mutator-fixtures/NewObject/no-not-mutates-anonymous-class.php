<?php

namespace NewObject_NotMutatesAnonymousClass;

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
