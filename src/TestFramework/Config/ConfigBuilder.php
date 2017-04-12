<?php

declare(strict_types=1);

namespace Infection\TestFramework\Config;


interface ConfigBuilder
{
    public function build();

    public function getPath() : string;
}