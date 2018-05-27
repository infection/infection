<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Events;

/**
 * @internal
 */
final class MutantsCreatingStarted
{
    /**
     * @var int
     */
    private $mutantCount;

    public function __construct(int $mutantCount)
    {
        $this->mutantCount = $mutantCount;
    }

    public function getMutantCount(): int
    {
        return $this->mutantCount;
    }
}
