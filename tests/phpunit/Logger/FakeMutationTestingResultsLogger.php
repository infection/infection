<?php

declare(strict_types=1);

namespace Infection\Tests\Logger;

use Infection\Logger\MutationTestingResultsLogger;
use Infection\Tests\UnsupportedMethod;

final class FakeMutationTestingResultsLogger implements MutationTestingResultsLogger
{
    public function log(): void
    {
        throw UnsupportedMethod::method(__CLASS__, __FUNCTION__);
    }
}
