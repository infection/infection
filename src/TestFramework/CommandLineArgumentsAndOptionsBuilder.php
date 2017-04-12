<?php

declare(strict_types=1);


namespace Infection\TestFramework;


interface CommandLineArgumentsAndOptionsBuilder
{
    public function build() : string;
}