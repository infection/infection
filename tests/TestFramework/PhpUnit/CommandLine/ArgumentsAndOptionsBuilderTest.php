<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpUnit\CommandLine;

use Infection\TestFramework\PhpUnit\CommandLine\ArgumentsAndOptionsBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ArgumentsAndOptionsBuilderTest extends TestCase
{
    public function test_it_builds_correct_command(): void
    {
        $configPath = '/config/path';
        $builder = new ArgumentsAndOptionsBuilder();

        $this->assertSame(
            [
                '--configuration',
                $configPath,
                '--stop-on-failure',
                '--verbose',
            ],
            $builder->build($configPath, '--verbose')
        );
    }
}
