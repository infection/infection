<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\PhpSpec;

use Infection\TestFramework\AbstractTestFrameworkAdapter;

/**
 * @internal
 */
final class Adapter extends AbstractTestFrameworkAdapter
{
    private const ERROR_REGEXPS = [
        '/Fatal error\:/',
        '/Fatal error happened/i',
    ];

    public function testsPass(string $output): bool
    {
        $lines = explode(PHP_EOL, $output);

        foreach ($lines as $line) {
            if (preg_match('%not ok \\d+ - %', $line)
                && !preg_match('%# TODO%', $line)) {
                return false;
            }
        }

        foreach (self::ERROR_REGEXPS as $regExp) {
            if (preg_match($regExp, $output)) {
                return false;
            }
        }

        return true;
    }

    public function getName(): string
    {
        return 'PhpSpec';
    }
}
