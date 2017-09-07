<?php
/**
 * Copyright © 2017 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\TestFramework\PhpSpec\Adapter;

use Infection\TestFramework\AbstractTestFrameworkAdapter;
use Infection\TestFramework\TestFrameworkTypes;

class PhpSpecAdapter extends AbstractTestFrameworkAdapter
{
    /**
     * @param string $output
     *
     * @return bool
     */
    public function testsPass(string $output): bool
    {
        $lines = explode("\n", $output);

        foreach ($lines as $line) {
            if (preg_match('%not ok \\d+ - %', $line)
                && !preg_match('%# TODO%', $line)) {
                return false;
            }
        }

        if (preg_match('/Fatal error happened/i', $output)) {
            return false;
        }

        return true;
    }

    public function getName(): string
    {
        return TestFrameworkTypes::PHPSPEC;
    }
}
