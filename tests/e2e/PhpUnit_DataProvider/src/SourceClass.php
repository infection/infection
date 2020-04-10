<?php

namespace PhpUnit_DataProvider;

class SourceClass
{
    /**
     * @var bool
     */
    private $value;

    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function factoryMethod(): self
    {
        return new self(true);
    }

    public function getValue(): bool
    {
        return $this->value;
    }
}
