<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Config;

interface InitialConfigBuilder
{
    public function build(string $version) : string;
}
