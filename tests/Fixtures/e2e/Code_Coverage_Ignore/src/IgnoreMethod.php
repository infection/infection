<?php

namespace Namespace_;

class IgnoreMethod
{
    /**
     * @codeCoverageIgnore
     */
    public function hello(): string
    {
        return 1 + 2;
    }

    public function foo(): string
    {
        return 'foo';
    }
}
