<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\TestFramework;

use Infection\Mutant\Mutant;

interface CommandLineArgumentsAndOptionsBuilder
{
    public function build(string $configPath, string $extraOptions, Mutant $mutant = null): string;
}
