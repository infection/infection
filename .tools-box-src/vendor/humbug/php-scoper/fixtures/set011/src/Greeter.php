<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Set011;

use Generator;
class Greeter
{
    private $words;
    public function __construct(array $words)
    {
        $this->words = $words;
    }
    public function greet()
    {
        foreach ($this->words as $word) {
            (yield $word . ' world!');
        }
    }
}
