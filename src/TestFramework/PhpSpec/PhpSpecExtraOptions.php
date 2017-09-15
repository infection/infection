<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\TestFramework\PhpSpec;

use Infection\TestFramework\TestFrameworkExtraOptions;

class PhpSpecExtraOptions extends TestFrameworkExtraOptions
{
    protected function getInitialRunOnlyOptions(): array
    {
        return [];
    }
}
