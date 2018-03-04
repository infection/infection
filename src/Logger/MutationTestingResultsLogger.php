<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Logger;

interface MutationTestingResultsLogger
{
    /**
     * Logs results of Mutation Testing to somewhere
     */
    public function log();
}
