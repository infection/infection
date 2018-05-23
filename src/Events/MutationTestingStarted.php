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
final class MutationTestingStarted
{
    /**
     * @var int
     */
    private $mutationCount;

    public function __construct(int $mutationCount)
    {
        $this->mutationCount = $mutationCount;
    }

    public function getMutationCount(): int
    {
        return $this->mutationCount;
    }
}
