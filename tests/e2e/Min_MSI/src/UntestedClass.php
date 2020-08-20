<?php

namespace Min_MSI;

class UntestedClass
{
    public function hello(): string
    {
        if (1 + 1 + 1) {
            return 'hello';
        }

        return '';
    }
}