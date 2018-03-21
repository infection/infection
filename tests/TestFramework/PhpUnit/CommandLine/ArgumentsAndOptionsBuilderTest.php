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

class ArgumentsAndOptionsBuilderTest extends TestCase
{
    public function test_it_builds_correct_command()
    {
        $configPath = '/config/path';
        $builder = new ArgumentsAndOptionsBuilder();

        $command = $builder->build($configPath, '--verbose');

        $this->assertContains('--stop-on-failure', $command);
        $this->assertContains('--verbose', $command);
        $this->assertContains(sprintf('--configuration %s', $configPath), $command);
    }
}
