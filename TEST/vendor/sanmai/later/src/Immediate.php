<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Later;

/**
@template
*/
final class Immediate implements Interfaces\Deferred
{
    private $output;
    public function __construct($input)
    {
        $this->output = $input;
    }
    public function get()
    {
        return $this->output;
    }
}
