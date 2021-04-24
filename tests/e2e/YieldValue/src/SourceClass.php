<?php

namespace YieldValue;

class SourceClass
{
    public function hello(): \Generator
    {
        $key = 'key';
        $value = 'value';

        $a = function () {
            $a = 'a';
            $b = 'b';
            (yield $a => $b);
        };

        yield $key => $value;
    }
}
