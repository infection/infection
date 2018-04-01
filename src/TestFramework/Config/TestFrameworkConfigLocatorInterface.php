<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Config;

/**
 * @see TestFrameworkConfigLocator
 */
interface TestFrameworkConfigLocatorInterface
{
    public function locate(string $testFrameworkName, string $customDir = null): string;
}
