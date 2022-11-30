<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\OndraM\CiDetector;

class Env
{
    public function get(string $name)
    {
        return \getenv($name);
    }
    public function getString(string $name) : string
    {
        return (string) $this->get($name);
    }
}
