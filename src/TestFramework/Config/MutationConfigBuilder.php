<?php

declare(strict_types=1);

namespace Infection\TestFramework\Config;

use Infection\Mutant\Mutant;
use Infection\TestFramework\Coverage\CodeCoverageData;

interface MutationConfigBuilder
{
    public function build(Mutant $mutant, CodeCoverageData $codeCoverageData) : string;
}