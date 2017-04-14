<?php

declare(strict_types=1);

namespace Infection\TestFramework\Config;


use Infection\Mutant\Mutant;

interface ConfigBuilder
{
    public function build(Mutant $mutant = null);
}