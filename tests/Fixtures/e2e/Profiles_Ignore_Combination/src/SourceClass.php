<?php

namespace ProfileIgnoreCombination;

class SourceClass
{
    public function hello(): string
    {
        return 'hello';
    }

    protected function bye(): string
    {
        return 'bye';
    }

    public function isTrue()
    {
        return true;
    }

    private function isFalse()
    {
        return false;
    }
}
