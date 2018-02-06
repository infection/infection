<?php
/**
 * Copyright © 2018 Tobias Stadler
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\TestFramework\Codeception\Adapter;

use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\TestFramework\TestFrameworkTypes;

class CodeceptionAdapter extends AbstractTestFrameworkAdapter
{
    const EXECUTABLE = 'codecept';
    const JUNIT_FILE_NAME = 'codeception.junit.xml';

    public function testsPass(string $output): bool
    {
        if (preg_match('/^FAILURES!/im', $output)) {
            return false;
        }

        if (preg_match('/^ERRORS!/im', $output)) {
            return false;
        }

        // OK (XX tests, YY assertions)
        $isOk = (bool) preg_match('/^OK\s\(/m', $output);

        // "OK, but incomplete, skipped, or risky tests!"
        $isOkWithInfo = (bool) preg_match('/^OK,/m', $output);

        // "Warnings!" - e.g. when deprecated functions are used, but tests pass
        $isWarning = (bool) preg_match('/^WARNINGS!/im', $output);

        return $isOk || $isOkWithInfo || $isWarning;
    }

    public function getName(): string
    {
        return TestFrameworkTypes::CODECEPTION;
    }
}
