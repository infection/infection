<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\TestFramework\PhpSpec\CommandLine;

use Infection\TestFramework\PhpSpec\CommandLine\ArgumentsAndOptionsBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ArgumentsAndOptionsBuilderTest extends TestCase
{
    public function test_it_builds_correct_command()
    {
        $configPath = '/config/path';
        $builder = new ArgumentsAndOptionsBuilder();

        $command = $builder->build($configPath, '--verbose');

        $this->assertContains('run', $command);
        $this->assertContains('--no-ansi', $command);
        $this->assertContains('--format=tap', $command);
        $this->assertContains('--stop-on-failure', $command);
        $this->assertContains('--verbose', $command);
        $this->assertContains(sprintf('--config=%s', $configPath), $command);
    }
}
