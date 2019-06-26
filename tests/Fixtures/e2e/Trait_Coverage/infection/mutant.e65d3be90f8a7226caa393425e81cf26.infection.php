<?php

namespace Trait_Coverage;

class SourceClass
{
    use SourceTrait;
    protected function hello() : string
    {
        return 'hello' . $this->world();
    }
}