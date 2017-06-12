<?php

declare(strict_types=1);

namespace Infection\TestFramework\Config;


interface InitialConfigBuilder
{
    public function build() : string;
}