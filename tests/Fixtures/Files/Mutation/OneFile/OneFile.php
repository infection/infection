<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Files\Mutation\OneFile;

class OneFile
{
    const FOO = 1 + 2;

    public function foo($value = true): int
    {
        return (function ($input = 33) {
            return $input;
        })() + 44;
    }
}
