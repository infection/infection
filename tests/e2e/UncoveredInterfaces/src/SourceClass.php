<?php

declare(strict_types=1);

namespace UncoveredInterfaces;

class SourceClass implements SourceInterface
{
    private $hello = 'hello';

    public function hello(): string
    {
        return $this->hello;
    }

    public function doSomething(int $value = 301): SourceInterface
    {
        $this->hello = (string) $value;

        return $this;
    }
}
