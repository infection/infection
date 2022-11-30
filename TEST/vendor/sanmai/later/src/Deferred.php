<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Later;

/**
@template
@psalm-suppress
*/
final class Deferred implements Interfaces\Deferred
{
    private $input;
    private $output;
    public function __construct(iterable $input)
    {
        $this->input = $input;
    }
    public function get()
    {
        if (null === $this->input) {
            return $this->output;
        }
        foreach ($this->input as $output) {
            $this->output = $output;
            break;
        }
        $this->input = null;
        return $this->output;
    }
}
