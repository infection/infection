<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Config;

use Infection\Mutant\Mutant;
use Infection\TestFramework\Coverage\CodeCoverageData;

interface MutationConfigBuilder
{
    public function build(Mutant $mutant) : string;
}