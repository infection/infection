<?php

declare(strict_types=1);

namespace Infection\TestFramework\Config;

use Infection\Mutant\Mutant;

interface MutationConfigBuilder
{
    public function build(Mutant $mutant) : string;
}