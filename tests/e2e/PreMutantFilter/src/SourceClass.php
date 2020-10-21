<?php

namespace PreMutantFilter;

class SourceClass
{
    private $a;

    public function __construct()
    {
        $this->a = 0;
    }

    public function hello(): string
    {
        ++$this->a;

        return 'hello';
    }
}
