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
final class MutationGeneratingStarted
{
    /**
     * @var int
     */
    private $mutableFilesCount;

    public function __construct(int $mutableFilesCount)
    {
        $this->mutableFilesCount = $mutableFilesCount;
    }

    public function getMutableFilesCount(): int
    {
        return $this->mutableFilesCount;
    }
}
