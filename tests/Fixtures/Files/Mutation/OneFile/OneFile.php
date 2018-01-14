<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Tests\Fixtures\Files\Mutation\OneFile;

class OneFile
{
    public function foo(): int
    {
        return 33 + 44;
    }
}
