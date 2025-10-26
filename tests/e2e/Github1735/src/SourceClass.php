<?php

namespace Github1735;

class SourceClass
{
    public function hello(): array
    {
        $var = [
            'class' => new class() {
                public function foo(): bool
                {
                    return true;
                }
            },
        ];

        return $var;
    }
}
