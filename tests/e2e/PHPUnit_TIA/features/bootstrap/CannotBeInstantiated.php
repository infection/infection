<?php

declare(strict_types=1);

namespace Infection\E2ETests\PHPUnitTIA\Behat;

// Kept local so the standalone fixture does not depend on Infection's autoloader.
trait CannotBeInstantiated
{
    private function __construct()
    {
    }
}
