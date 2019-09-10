<?php

namespace Trait_Coverage;


class SourceClass
{
    use SourceTrait;

    public function hello(): string
    {
        return 'hello'. $this->world();
    }
}
